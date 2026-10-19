# Progres ERP F5

Pembaruan awal: 19 Oktober 2026, berdasarkan bukti dan laporan di percakapan sampai pukul 19.35 WIB. Ini catatan serah terima, bukan pemeriksaan langsung seluruh isi repo atau komputer anggota. Perbarui setelah pekerjaan nyata selesai.

Konteks dan pembagian tetap ada di [PROJECT_CONTEXT.md](PROJECT_CONTEXT.md). Aturan pengerjaan ada di [AGENTS.md](AGENTS.md).

## Arti status

- **Terverifikasi**: ada hasil perintah, berkas, atau bukti yang sudah diperiksa.
- **Dilaporkan**: pengguna/kelompok menyatakan sesuatu, tetapi implementasi terkait belum diperiksa.
- **Direncanakan**: ada arahan atau tugas, belum ada hasil pelaksanaan.
- **Menunggu**: bergantung pada jawaban atau bukti tertentu.
- **Belum diverifikasi**: mungkin sudah dikerjakan, tetapi belum ada bukti pada catatan ini.

Jangan mengubah status menjadi selesai tanpa mencatat hasil dan bukti. Bedakan selesai membuat kode, berhasil menjalankan, dan lulus alur lengkap.

## Ringkasan saat ini

Kontrak rekap v0.2, contoh respons, dan mock Python sudah dibuat serta pernah diuji. Aplikasi Laravel 13.7.0 sudah berhasil dipasang pada mesin Dhaffa. Implementasi UI rekap dan endpoint rekap Laravel belum ditunjukkan. Pemasangan/aktivasi Blaze dan pembukaan halaman Laravel belum dikonfirmasi.

Coding ditangani Muhamad Rizky untuk frontend dan Arinal Haq untuk backend. Dhaffa menangani kontrak/koordinasi, Danny pengujian/laporan, dan Zaki bukti/README.

## Daftar pekerjaan

| ID | Pekerjaan | Pemilik | Status dan langkah berikutnya |
| --- | --- | --- | --- |
| P-01 | OpenAPI rekap v0.2 dan contoh respons | Dhaffa, bersama pengembang jika kontrak berubah | Terverifikasi tersedia. Pertahankan sebagai acuan sementara sambil menunggu E-2. |
| P-02 | Mock Python dan validasi awal | Paket awal F-5 | Terverifikasi pernah lulus 10 pengujian dan validator. Hasil historis, bukan bukti implementasi Laravel lulus. |
| P-03 | Push awal repo GitHub | Dhaffa | Terverifikasi dari screenshot push. Isi remote terbaru belum diperiksa ulang. |
| P-04 | Laravel di folder web | Arinal, pemasangan awal oleh Dhaffa | Terverifikasi versi 13.7.0 melalui php artisan --version. |
| P-05 | npm install, build, dan halaman Laravel | Arinal, Rizky | Direncanakan. Perintah sudah diberikan, hasil belum dikonfirmasi. |
| P-06 | Pemasangan dan aktivasi Blaze | Arinal dan Rizky, dibantu Dhaffa | Direncanakan. Cek composer show livewire/blaze, provider, cache view, lalu render komponen yang memakai Blaze. |
| P-07 | Frame Figma dan komponen bersama | Rizky | Menunggu penentuan link/frame desain terbaru. |
| P-08 | UI rekap biaya | Rizky | Direncanakan. Implementasi belum ditunjukkan. Gunakan fixture respons untuk mulai. |
| P-09 | Endpoint rekap Laravel dan akses | Arinal | Direncanakan. Respons 17 field dan error harus sesuai OpenAPI. |
| P-10 | Data demo dan sumber periode | Arinal, Rizky untuk pemakaian UI | Fixture JSON tersedia. Pemakaian di Laravel dan sumber daftar periode belum ditentukan. |
| P-11 | Integrasi UI dengan endpoint Laravel | Rizky dan Arinal | Direncanakan. Uji alur berhasil, kosong, termin saja, dan ditolak. |
| P-12 | Review konsumen E-2 | Dhaffa | Menunggu balasan. Pengguna menyatakan link repo sudah dikirim. |
| P-13 | Review F-4 | Dhaffa, koordinasi dengan F-4 | Kesepakatan isi dilaporkan. Perlu kontrak revisi/commit dan pengujian request. |
| P-14 | Kontrak F-1, F-2, dan F-3 | Dhaffa berkoordinasi dengan tim | Kontrak belum diterima/diperiksa dalam sesi ini. Chat permintaan F-3 telah disusun, pengiriman belum dikonfirmasi. |
| P-15 | Dokumen kontrak API final | Dhaffa | Direncanakan revisi. File yang diperiksa masih memuat status/tenggat lama. |
| P-16 | Skenario uji dan laporan demo | Danny | Direncanakan. Template sudah dibaca, hasil demo Laravel belum tersedia. |
| P-17 | Bukti, README, dan commit demo | Zaki | Direncanakan. Kumpulkan setelah uji aktual dan samakan panduan dengan proyek terbaru. |
| P-18 | Pedoman dan konteks bersama | Dhaffa | Ketiga file disusun di sesi ini. Penempatan pada komputer dan commit/push ke repo belum dikonfirmasi. |

## Bukti yang sudah diketahui

Bukti berikut berasal dari percakapan. Jangan menganggap seluruhnya sudah tersimpan pada folder repo `bukti/`. Pindahkan atau tautkan bukti yang diperlukan dengan lokasi yang benar sebelum pengumpulan.

| Bukti | Yang dibuktikan | Batas |
| --- | --- | --- |
| Screenshot 13 Oktober 2026, request mock | Respons 200, 400, 401, 403, dan 404 pernah ditampilkan. | Mock Python, bukan backend Laravel atau layanan produsen nyata. |
| Screenshot 13 Oktober 2026, unittest | 10 pengujian mock lulus. | Belum membuktikan source versi terbaru masih lulus tanpa uji ulang yang relevan. |
| Screenshot 13 Oktober 2026, validator | Validasi kontrak/contoh respons lulus. | Bukan bukti review E-2 atau keamanan seluruh aplikasi. |
| Screenshot 13 Oktober 2026, git push | Push branch main berhasil, commit awal yang ditunjukkan c4ba1df. | Bukan commit demo final dan jangan diasumsikan HEAD terbaru. |
| image(20261019-120514).png | Composer 2.9.2, PHP 8.4.15, Node 25.2.1, npm 11.6.2 dikenali. | Mesin Dhaffa, bukan seluruh anggota. |
| image(20261019-121734).png | Laravel Framework 13.7.0 pada folder ERP-F5/web. | Bukan bukti halaman browser terbuka atau alur rekap selesai. |
| image(20261019-121807).png | Folder web berada sejajar dengan examples, tests, dokumen, dan bukti. | Tidak membuktikan semua file sudah di-commit/push. |
| Pesan F-4 disampaikan 18 Oktober 2026 | Usulan workingDaysInRange, paid/leaveType, rentang inklusif, dan tambahan contoh 400 disepakati. | Kontrak revisi dan perilaku server belum diverifikasi. |

## Langkah berikutnya berurutan

1. Simpan ketiga file pedoman di root ERP-F5. Periksa dan commit setelah isinya sesuai keadaan tim.
2. Pastikan penambahan aplikasi `web/` dibagikan lewat repo tanpa vendor, node_modules, .env rahasia, atau database berisi data pribadi. Verifikasi .gitignore dan diff sebelum staging.
3. Konfirmasi pemasangan aset dan pembukaan Laravel pada port 8001. Catat hasil aktual, jangan melewati kegagalan instalasi.
4. Jika Blaze sudah dipasang, periksa paket dan konfigurasi yang benar-benar ada. Uji komponen anonim di folder yang diaktifkan. Paket terpasang saja belum membuktikan optimasi aktif.
5. Tentukan frame Figma dan bentuk sumber pilihan periode. Gunakan satu set ID fixture dari konteks agar frontend dan backend cocok.
6. Rizky membuat UI dengan contoh respons, Arinal membuat endpoint rekap. Keduanya bekerja paralel setelah kontrak dan batas file jelas.
7. Hubungkan UI ke backend Laravel, lalu Danny menguji dan Zaki mengumpulkan bukti.
8. Dhaffa menindaklanjuti E-2 dan F-4 sambil memperbarui kontrak tertulis. Bagian yang belum disepakati tetap terbuka.
9. Lengkapi laporan hanya dengan hasil aktual, catat commit demo yang benar-benar diuji, dan periksa panduan dari checkout yang dipakai tim.

## Acuan menjalankan dan pemeriksaan

Perintah berikut adalah panduan, bukan hasil pengujian. Periksa konfigurasi dan README aktual lebih dahulu. Jangan mengulang instalasi jika dependensi sudah sesuai lockfile.

### Laravel pada folder web

```bash
composer install
php artisan --version
```

Jika konfigurasi lokal belum ada, salin `.env.example` menjadi `.env` dan buat kunci aplikasi lokal. Jangan mengganti kunci aplikasi yang sudah dipakai tanpa alasan.

```bash
npm install
npm run build
php artisan serve --port=8001
```

Gunakan `npm ci` sebagai pengganti `npm install` untuk pemasangan ulang yang mengikuti package-lock.json. Setelah server berjalan, buka http://127.0.0.1:8001. Untuk pengembangan Vite langsung, ikuti konfigurasi proyek dan jalankan server Vite pada terminal terpisah jika diperlukan.

### Blaze jika pemasangan sudah dikerjakan

```bash
composer show livewire/blaze
php artisan view:clear
```

Panduan sebelumnya mengarahkan impor `Livewire\Blaze\Blaze` di `web/app/Providers/AppServiceProvider.php` dan pemanggilan berikut di dalam `boot()`:

```php
Blaze::optimize()->in(resource_path('views/components/ui'));
```

Jangan menimpa isi provider yang sudah diubah anggota lain. Konfigurasi ini menargetkan komponen Blade anonim di folder tersebut, bukan otomatis seluruh halaman. Uji melalui tag komponen Blade dan pastikan tampilan sesuai.

### Pemeriksaan implementasi

- Laravel di `web/`: `php artisan test`, build frontend, dan uji browser sesuai perubahan.
- Python di root: `python -m unittest discover -s tests -v` dan `python validate_contract.py`, memakai environment proyek yang sudah disiapkan.
- Mock Python dijalankan menurut README root dan dapat memakai port 8000, terpisah dari Laravel 8001.
- Jangan menganggap pengujian mock Python membuktikan endpoint Laravel sudah bekerja.
- Skenario angka utama harus cocok dengan fixture dan schema. Total biaya tetap null, bonus tidak ditambah ulang.

## Hambatan dan pekerjaan yang tetap bisa berjalan

| Hal yang ditunggu | Dampak | Yang bisa dilanjutkan |
| --- | --- | --- |
| Review E-2 dan Q-17 | Total biaya dan filter status final belum diputuskan. | UI/backend mengikuti kontrak v0.2 dengan totalBiaya null. |
| Kontrak revisi F-4 | Integrasi cuti belum bisa dinyatakan lulus. | Review dokumen dan demo rekap menggunakan sumber data internal/fixture. |
| F-1 belum terintegrasi | Autentikasi nyata belum terbukti. | Token/akun demo yang dinyatakan sebagai mock dan pemeriksaan akses lokal. |
| Kontrak F-2/F-3 belum diperiksa | Integrasi data payroll sumber belum terbukti. | Fixture fiktif yang jelas dan dapat diulang. |
| Frame Figma belum ditentukan | Detail visual belum memiliki acuan yang bisa diperiksa pengembang. | Penyiapan struktur, koneksi API, data, dan komponen nonvisual. |

## Kriteria selesai demo

- [ ] UI memilih periode dan mengambil data dari endpoint yang benar.
- [ ] Rekap berisi data cocok dengan schema dan hitungan pembanding.
- [ ] Periode kosong ditampilkan dengan benar.
- [ ] Termin saja tidak dianggap tidak ada data.
- [ ] Satu penolakan nyata dari backend ditampilkan oleh UI.
- [ ] Loading dan kegagalan tidak menyisakan hasil lama yang menyesatkan.
- [ ] Mock, sumber data, dan batas demo dijelaskan secara jujur.
- [ ] README dapat diikuti dan bukti memiliki lokasi yang dapat diperiksa.
- [ ] Versi OpenAPI serta commit demo dicatat sesuai yang diuji.
- [ ] Laporan memuat hasil aktual dan status review silang yang benar.

## Format pembaruan sesi

Tambahkan catatan berikut setelah sesi kerja. Ubah daftar pekerjaan di atas jika statusnya berubah. Jangan mengisi placeholder sebagai bukti selesai.

```text
Tanggal dan jam:
Anggota dan branch:
ID pekerjaan:
Perubahan dan file terkait:
Perintah atau skenario yang benar-benar dijalankan:
Hasil aktual:
Lokasi bukti:
Commit yang diuji, jika sudah ada:
Hambatan:
Langkah berikutnya dan pemilik:
```
