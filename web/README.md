# ERP F-5 — Aplikasi Laravel (web/)

Aplikasi web ERP PT Anaheim Nimbus Universal, Kelompok 5 Kelas F, modul payroll dan rekap biaya.

**Catatan penting:** Backend saat ini menggunakan fixture demo dan autentikasi mock. Belum ada integrasi nyata dengan modul F-1 (autentikasi), F-2 (karyawan), F-3 (kehadiran), atau F-4 (cuti). Token demo menggantikan autentikasi F-1 sampai integrasi tersedia.

---

## Prasyarat

| Perangkat lunak | Versi yang diuji | Catatan |
| --- | --- | --- |
| PHP | 8.4.15 (via Herd) | PHP 8.2+ diperlukan Laravel 13 |
| Composer | 2.9.2 | Untuk dependensi PHP |
| Node.js | 25.2.1 | Untuk build aset frontend |
| npm | 11.6.2 | Untuk dependensi JS |

---

## Instalasi awal (clone baru)

Semua perintah dijalankan di dalam folder `web/` kecuali disebutkan lain.

### 1. Masuk ke folder aplikasi

```bash
cd web
```

### 2. Pasang dependensi PHP sesuai lockfile

```bash
composer install
```

Gunakan `composer install` (bukan `composer update`) agar versi mengikuti `composer.lock`.

### 3. Buat file konfigurasi lokal

Jika `.env` belum ada, salin dari contoh:

```bash
copy .env.example .env
```

Pada macOS/Linux gunakan `cp .env.example .env`.

### 4. Buat kunci aplikasi (hanya untuk instalasi awal)

```bash
php artisan key:generate
```

> **Jangan jalankan `key:generate` jika `.env` sudah memuat `APP_KEY` yang terisi.** Perintah ini hanya untuk instalasi pertama atau jika `.env` masih kosong. Mengganti kunci yang sudah ada akan membatalkan semua sesi yang sedang berjalan.

### 5. Siapkan database SQLite

Aplikasi dikonfigurasi menggunakan SQLite untuk kemudahan demo lokal.

```bash
# Buat file database jika belum ada
type nul > database\database.sqlite
```

Pada macOS/Linux:

```bash
touch database/database.sqlite
```

Kemudian jalankan migrasi:

```bash
php artisan migrate
```

> **Jangan gunakan `migrate:fresh` sebagai langkah rutin.** Gunakan `migrate:fresh` hanya jika Anda ingin mereset database dari awal secara sengaja.

### 6. Pasang dependensi JavaScript sesuai lockfile

```bash
npm ci
```

Gunakan `npm ci` (bukan `npm install`) agar versi mengikuti `package-lock.json`.

---

## Menjalankan untuk pengembangan

### Server Laravel (port 8001)

Port 8001 dipakai agar tidak bertabrakan dengan mock Python di port 8000.

```bash
php artisan serve --port=8001
```

Buka browser ke: http://127.0.0.1:8001

### Server Vite (mode pengembangan, opsional)

Jika ingin hot-reload aset saat mengembangkan frontend, jalankan Vite di terminal terpisah:

```bash
npm run dev
```

Biarkan kedua terminal (`php artisan serve` dan `npm run dev`) berjalan secara bersamaan.

### Build aset untuk produksi atau verifikasi

```bash
npm run build
```

Hasil build tersimpan di `public/build/`. Diperlukan agar aset tersedia tanpa server Vite.

---

## Menjalankan pengujian Laravel

```bash
php artisan test
```

Atau secara eksplisit dengan PHPUnit:

```bash
./vendor/bin/phpunit
```

Pengujian berada di `web/tests/Feature/`. Terpisah dari pengujian Python di root.

---

## Endpoint API demo

### 1. Rekap Biaya (FTR-K05-09 / API-K05-01)

```
GET /api/payroll/v1/rekap-biaya?periodeId=<UUID>
Authorization: Bearer <token>
```

| Token demo | Peran | Akses |
| --- | --- | --- |
| `mock-manajemen` | manajemen | Diizinkan (200) |
| `mock-keuangan` | petugas_keuangan | Diizinkan (200) |
| `mock-e2` | layanan_e2 | Diizinkan (200) |
| `mock-karyawan` | karyawan | Ditolak (403) |
| `mock-tenaga-ahli` | tenaga_ahli | Ditolak (403) |
| Token lain / kosong | tidak dikenal | Tidak terotorisasi (401) |

### 2. Periode Payroll (FTR-K05-01 / UI-K05-01 / API-K05-02 s.d API-K05-05)

Halaman antarmuka: `http://127.0.0.1:8001/periode-payroll`

```
GET   /api/payroll/v1/periode              # Daftar periode (DB)
GET   /api/payroll/v1/periode/{periodeId}  # Detail periode (DB)
POST  /api/payroll/v1/periode              # Buat periode baru (Petugas/Admin Payroll)
PUT   /api/payroll/v1/periode/{periodeId}  # Perbarui periode penuh (Petugas/Admin Payroll)
PATCH /api/payroll/v1/periode/{periodeId}  # Perbarui periode parsial (Petugas/Admin Payroll)
```

| Token demo | Peran | Akses Baca (GET) | Akses Tulis (POST/PUT/PATCH) |
| --- | --- | --- | --- |
| `mock-payroll` / `mock-petugas-payroll` | petugas_payroll | Diizinkan (200) | Diizinkan (201/200) |
| `mock-admin-payroll` | admin_payroll | Diizinkan (200) | Diizinkan (201/200) |
| `mock-manajemen` | manajemen | Diizinkan (200) | Ditolak (403) |
| `mock-keuangan` | petugas_keuangan | Diizinkan (200) | Ditolak (403) |
| `mock-e2` | layanan_e2 | Diizinkan (200) | Ditolak (403) |
| `mock-karyawan` | karyawan | Ditolak (403) | Ditolak (403) |

### ID periode fixture demo & database

| Skenario | periodeId |
| --- | --- |
| Payroll & termin | `01a1193d-2500-7c04-854b-8bf03c711dde` |
| Periode kosong | `01a1193d-2500-7c05-854b-8bf03c711dde` |
| Termin saja | `01a1193d-2500-7c06-854b-8bf03c711dde` |

Untuk mengisi data awal ke database SQLite:
```bash
php artisan db:seed --class=PayrollPeriodSeeder
```

UUID tidak dikenal tapi format valid → 404. Format salah / kosong / duplikat → 400. Validasi input gagal → 422.

Contoh dengan `curl.exe` di PowerShell:

```powershell
curl.exe -i -H "Authorization: Bearer mock-payroll" "http://127.0.0.1:8001/api/payroll/v1/periode"
```

---

## Catatan teknis

- `totalBiaya` selalu `null` sesuai kontrak draf v0.2 (menunggu kesepakatan Q-17). UI menampilkan "Belum ditetapkan".
- Bonus sudah termasuk dalam `totalPendapatan`, tidak ditambahkan lagi.
- `totalPotongan` adalah potongan komponen karyawan di luar BPJS. `totalBpjsKaryawan` dicatat terpisah.
- Pemformatan rupiah dilakukan di frontend. Nilai JSON berupa angka tanpa pemisah ribuan.
- UUID pencarian bersifat case-insensitive (huruf besar/kecil dianggap sama).
- Periode baru yang dibuat di database otomatis terintegrasi dengan Rekap Biaya dan mengembalikan respons `adaData: false`, agregat 0, dan `totalBiaya: null` jika belum memiliki transaksi.

---

## Struktur folder utama

```
web/
├── app/
│   ├── Http/Controllers/Api/
│   │   ├── RekapBiayaController.php                    # Endpoint rekap
│   │   └── PayrollPeriodController.php                 # Endpoint periode (CRUD)
│   ├── Models/
│   │   └── PayrollPeriod.php                           # Model Eloquent periode payroll
│   └── Services/RekapBiayaFixtureService.php           # Fixture demo backend
├── database/
│   ├── migrations/                                     # Migrasi tabel payroll_periods
│   └── seeders/PayrollPeriodSeeder.php                 # Seeder periode awal
├── resources/
│   ├── js/
│   │   ├── app.js                                      # Entrypoint JS
│   │   ├── rekap-biaya.js                              # Modul frontend rekap
│   │   └── periode-payroll.js                          # Modul frontend periode
│   └── views/
│       ├── components/layouts/app.blade.php            # Layout bersama & navigasi
│       ├── rekap-biaya.blade.php                       # Halaman rekap biaya
│       └── periode-payroll.blade.php                   # Halaman periode payroll
├── routes/
│   ├── api.php                                         # Route API
│   └── web.php                                         # Route halaman web
└── tests/Feature/
    ├── ExampleTest.php                                 # Uji dasar
    ├── RekapBiayaApiTest.php                           # Uji endpoint rekap
    └── PayrollPeriodApiTest.php                        # Uji endpoint periode payroll
```
