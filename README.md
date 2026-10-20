# OpenAPI Payroll F-5

Draf v0.2, 13 Oktober 2026. Kelompok 5 Kelas F.

Paket ini menerjemahkan API-K05-01 pada dokumen minggu 2 menjadi kontrak OpenAPI dan mock HTTP lokal. Endpoint mengirim agregat biaya per periode untuk E-2 dan mendukung UI-K05-09. Angka dan token seluruhnya fiktif. Kontrak masih memerlukan review E-2. Penyusunan pertanyaan mitra dikerjakan terpisah.

## Isi paket

- `openapi.yaml` adalah artefak kontrak utama, memakai OpenAPI 3.0.3.
- `openapi.json` memuat kontrak yang sama dalam JSON.
- `examples/` memuat tiga respons berhasil dan empat respons gagal.
- `mock_server.py` menjalankan endpoint dengan Python standard library.
- `tests/test_mock.py` menguji perilaku melalui request HTTP nyata.
- `validate_contract.py` memeriksa kontrak dan contoh dengan validator OpenAPI.
- `dokumen/` memuat dokumen pertemuan 3 sesuai template.
- `bukti/` memuat hasil uji lokal dan rekaman respons endpoint.

## Menjalankan mock

Gunakan Python 3.10 atau lebih baru. Ekstrak ZIP, buka terminal di folder ini, lalu jalankan:

```bash
python mock_server.py
```

Pada Windows, gunakan `py mock_server.py` jika perintah `python` belum tersedia. Pada macOS/Linux, bisa memakai `python3`. Tidak perlu menginstal framework, database, atau dependency tambahan untuk menjalankan mock.

Alamat default: `http://127.0.0.1:8000`. Jika port digunakan aplikasi lain, jalankan `python mock_server.py --port 8001` dan gunakan port yang sama saat mengirim request. Tekan Ctrl+C untuk menghentikan server.

## Menjalankan Laravel demo pada port 8001

Di folder `web/`, pastikan dependensi sudah sesuai package-lock/composer.lock lalu jalankan:

```bash
composer install
npm install
php artisan key:generate
php artisan serve --port=8001
```

Jika `.env` belum ada, buat salinan dari `.env.example` terlebih dahulu. Aplikasi demo ini memakai mock token untuk autentikasi lokal dan bukan integrasi nyata F-1. Token demo yang diizinkan: `mock-manajemen`, `mock-keuangan`, dan `mock-e2`. Token `mock-karyawan` dan `mock-tenaga-ahli` akan ditolak dengan HTTP 403. Header kosong atau token tidak dikenal mendapat HTTP 401.

Untuk pengujian API dari terminal:

```bash
curl -i -H "Authorization: Bearer mock-e2" "http://127.0.0.1:8001/api/payroll/v1/rekap-biaya?periodeId=01a1193d-2500-7c04-854b-8bf03c711dde"
```

## Mencoba request

Pada Postman atau klien HTTP lain:

1. Pilih GET.
2. Masukkan `http://127.0.0.1:8000/api/payroll/v1/rekap-biaya`.
3. Tambahkan query `periodeId` dengan salah satu ID contoh di bawah.
4. Tambahkan header `Authorization` dengan nilai `Bearer mock-e2`.
5. Kirim request. Contoh berhasil menggunakan HTTP 200.

Contoh terminal:

```bash
curl -i -H "Authorization: Bearer mock-e2" "http://127.0.0.1:8000/api/payroll/v1/rekap-biaya?periodeId=01a1193d-2500-7c04-854b-8bf03c711dde"
```

Di Windows PowerShell, gunakan `curl.exe` untuk contoh tersebut.

| Data contoh | periodeId | Respons |
| --- | --- | --- |
| Payroll dan termin | `01a1193d-2500-7c04-854b-8bf03c711dde` | 200, `examples/success.json` |
| Periode kosong | `01a1193d-2500-7c05-854b-8bf03c711dde` | 200, `examples/success-empty.json` |
| Termin saja | `01a1193d-2500-7c06-854b-8bf03c711dde` | 200, `examples/success-termin-only.json` |
| UUID tidak dikenal | `01a1193d-2500-7c07-854b-8bf03c711dde` | 404, `examples/error.json` |

Token demo yang diizinkan: `mock-manajemen`, `mock-keuangan`, `mock-e2`. Token `mock-karyawan` dan `mock-tenaga-ahli` dikenali tetapi ditolak dengan 403. Token lain atau header yang kosong mendapat 401. Ini simulasi peran, belum autentikasi F-1 yang sesungguhnya.

`periodeId` wajib diberikan satu kali dan berbentuk UUID dengan tanda hubung. Input kosong, salah format, atau duplikat mendapat 400. Token diperiksa dahulu. Parameter query tambahan tidak dipakai. GET tidak menerima body dan tidak mengubah data. Metode POST/PUT/PATCH/DELETE ditolak dengan 405. Tidak ada paging atau pengurutan karena hasilnya satu objek per periode.

## Aturan data dari minggu 2

Sumber rekap: ENT-K05-01 (periode), ENT-K05-08/09 (hasil dan rincian payroll), ENT-K05-13/15 (termin dan pencatatan pembayaran).

- Bonus merupakan bagian dari totalPendapatan, tidak ditambah ulang.
- totalPotongan hanya potongan komponen. BPJS karyawan memakai totalBpjsKaryawan terpisah.
- Contoh gaji bersih: 7.750.000 - 200.000 - 65.000 = 7.485.000.
- BPJS perusahaan 260.000 ditampilkan terpisah dan tidak mengurangi gaji bersih.
- Pembayaran termin contoh 25.000.000 ditampilkan terpisah.
- totalBiaya selalu null pada draf ini sampai Q-17 disepakati. Mengisi 0 akan keliru karena definisinya belum tersedia.
- Periode kosong tetap 200, adaData=false, jumlah dan agregat 0, totalBiaya=null.
- Periode termin saja tetap adaData=true, jumlahKaryawan dan agregat payroll 0.
- Rentang tanggal contoh inklusif dan fiktif. Dasar periode termin menurut tanggalBayar masih usulan Q-12.
- Status periode: Draf, Dihitung, Menunggu Persetujuan, Disetujui. Cakupan status untuk rekap final masih menunggu Q-17.
- Waktu respons menggunakan ISO 8601 dengan WIB +07:00. Timestamp mock tetap agar pengulangan request menghasilkan fixture yang sama.
- Nominal berupa angka JSON, tanpa pemisah ribuan, maksimal dua desimal. Konsumen memakai perhitungan desimal ketika mengolah uang.
- Respons tidak mengirim employeeId, nama, rekening, NPWP, gaji pokok per orang, atau slip individual.

Mock hanya membaca fixture yang sudah disiapkan. Mock belum menarik data modul lain, menjalankan rumus payroll, menerbitkan slip, atau mengagregasi database.

## Menguji dan memvalidasi

Uji HTTP tidak memerlukan dependency tambahan:

```bash
python -m unittest discover -s tests -v
```

Untuk validasi formal kontrak dan semua contoh, buat virtual environment bila diperlukan, lalu:

```bash
python -m pip install -r requirements-dev.txt
python validate_contract.py
```

Validator memeriksa struktur OpenAPI, referensi skema, format data, tipe, field wajib, dan kesamaan contoh di YAML/JSON dengan folder examples. Hasil lokal disimpan dalam folder bukti. Uji otomatis ini belum menggantikan review kelompok konsumen.

## Pertanyaan yang tetap terbuka

| ID | Konfirmasi | Koordinasi | Target |
| --- | --- | --- | --- |
| Q-12 | Dasar pengelompokan pembayaran termin ke periode | Dhaffa Galang Fahriza (usulan) dengan mitra | Belum ditetapkan |
| Q-17 | Definisi total biaya, status yang masuk rekap, dan format data E-2 | Dhaffa Galang Fahriza (usulan) dengan E-2 | Belum ditetapkan |
| Q-14 | Apakah ID Proyek dan penugasan PM tersedia dalam data kontrak | Zaki Ramadhan Wijaya (usulan) dengan F-2 | Belum ditetapkan |
| OPEN-01 | Autentikasi dan format galat final | Perwakilan F-1/F-5/E-2 belum ditetapkan | Belum ditetapkan |

Q-14 mendukung sumber data termin, bukan parameter baru untuk endpoint rekap. Dependensi API F-5 tetap F-1 sampai F-4. Tidak ada panggilan langsung ke API D-1.

## Review dan pengumpulan

Reviewer internal dan perwakilan E-2 belum ditetapkan. Kolom temuan, keputusan, dan revisi pada dokumen tetap kosong sampai review benar-benar dilakukan. Checklist pengumpulan juga belum ditandai.

Unggah isi folder ini ke repositori kelompok, lalu isi URL repositori pada dokumen. Minta E-2 mencoba request atau membangun mock/client dari kontrak, catat hasilnya, dan perbarui kontrak bila disepakati. Tautan repositori belum tersedia, paket belum dipublikasikan. Tenggat tugas OpenAPI mengikuti instruksi dosen, belum diberikan dalam template. Tenggat 14 Oktober pada gambar yang dikirim berlaku untuk tugas update pertanyaan mitra.

## Acuan

- Spesifikasi Fitur Minimum minggu 2 v0.2, FTR-K05-09.
- Rancangan Antarmuka minggu 2 v0.2, UI-K05-09.
- Skema Data Awal minggu 2 v0.2, berdasarkan skema teman kelompok.
- Daftar Kontrak API minggu 2 v0.2, API-K05-01 dan revisi Q-14.
- Template Tugas Pertemuan 3 OpenAPI Versi Awal.
- OpenAPI Specification 3.0.3: https://spec.openapis.org/oas/v3.0.3.html
