# Konteks proyek ERP F5

Diperbarui pada 19 Oktober 2026 berdasarkan percakapan tim yang disampaikan Dhaffa. File ini merangkum keputusan dan batas proyek, bukan pengganti source code atau kontrak OpenAPI. Status pekerjaan ada di [PROGRESS.md](PROGRESS.md), aturan kerja ada di [AGENTS.md](AGENTS.md).

## Identitas dan sumber

- Mata kuliah: Proyek Perangkat Lunak, Kelompok 5 Kelas F.
- Kasus: ERP PT Anaheim Nimbus Universal.
- Modul: payroll, slip gaji, dan pembayaran tenaga ahli.
- Repository: https://github.com/GalangDhaffa/Proyek_Perangkat_Lunak_F-5
- Sumber kebutuhan: backlog dan dokumen minggu 2, spesifikasi fitur minimum, rancangan UI, skema data, serta daftar kontrak API yang telah direvisi.
- Skema data awal hasil pekerjaan teman yang dipilih pengguna tetap menjadi acuan. Jangan mengganti model data dengan rancangan baru tanpa kebutuhan yang jelas.
- Acuan teknis rekap: `openapi.yaml` v0.2, salinan JSON, dan contoh respons di `examples/`.
- Acuan visual: prototype Figma terbaru yang ditentukan tim. Link/frame final belum diberikan dalam percakapan ini.
- Acuan tugas sesi 4: screenshot Kontrak API Final untuk Asesmen UTS dan template Laporan Demo Alur Lokal Pertama. Dokumen tugas tetap menjadi sumber persyaratan resmi.

## Cakupan modul dan demo aktif

Cakupan keseluruhan F-5 mencakup sembilan fitur:

1. FTR-K05-01: periode dan pengambilan data payroll.
2. FTR-K05-02: gaji pokok, komponen, dan bonus.
3. FTR-K05-03: konfigurasi serta riwayat BPJS.
4. FTR-K05-04: perhitungan, pratinjau, dan hitung ulang payroll.
5. FTR-K05-05: pengajuan dan persetujuan payroll.
6. FTR-K05-06: pratinjau, penerbitan, dan koreksi slip PDF.
7. FTR-K05-07: akses dan unduh slip sendiri.
8. FTR-K05-08: termin tenaga ahli.
9. FTR-K05-09: rekap biaya per periode.

Fokus kerja demo pertama yang sedang dipersiapkan adalah nomor 9, dengan UI-K05-09 dan API-K05-01. Alurnya: pilih periode, minta rekap, tampilkan hasil. Ini pilihan alur kelompok, bukan pernyataan bahwa dosen mewajibkan fitur nomor 9.

Template meminta satu alur utama yang berjalan, satu kasus berhasil, satu kasus gagal/ditolak, hasil aktual, dan bukti. Dependensi yang belum tersedia boleh memakai mock sesuai kontrak produsennya. Seluruh aplikasi tidak harus selesai untuk demo lokal pertama.

## Pembagian pelaksanaan terbaru

| Anggota | Bagian | Hasil yang ditangani |
| --- | --- | --- |
| Muhamad Rizky | Frontend dan UI | Halaman rekap sesuai Figma, pilihan periode, pemanggilan API, format rupiah, loading, data kosong, dan error. |
| Arinal Haq | Backend dan data | Aplikasi Laravel, endpoint rekap, validasi, akses, agregat, serta penyimpanan atau data fixture. |
| Dhaffa Galang Fahriza | Kontrak dan koordinasi | Dokumen kontrak final, catatan review, koordinasi E-2 dan kelompok dependensi, serta penyelarasan dokumen. |
| Danny Lukman | Pengujian dan laporan | Skenario, angka pembanding, uji aktual, dan laporan demo. |
| Zaki Ramadhan Wijaya | Bukti dan panduan | Screenshot/rekaman/log, folder bukti, README, dan commit demo. |

Pengguna memutuskan coding dilakukan dua orang, anggota lainnya menangani dokumen dan bukti. Pembagian ini menggantikan usulan coding berlima dan usulan sebelumnya yang menempatkan Arinal di frontend. Penanggung jawab fitur pada dokumen minggu 2 tetap menjadi referensi penelusuran kebutuhan, bukan pembagian coding terbaru.

## Teknologi dan lokasi

| Bagian | Keputusan atau kondisi yang diketahui |
| --- | --- |
| Aplikasi utama | Laravel, satu aplikasi pada subfolder `web/`. |
| Versi lokal yang terbukti | Laravel Framework 13.7.0, dari `php artisan --version`. |
| PHP lokal | 8.4.15 melalui Herd, terlihat pada keluaran Composer. |
| Composer lokal | 2.9.2. |
| Node.js dan npm lokal | Node.js 25.2.1 dan npm 11.6.2. Ini catatan mesin Dhaffa, bukan bukti versi seluruh anggota. |
| Tampilan | Blade dibahas sebagai pola tampilan Laravel. Jangan memasang React/Vue atau starter kit lain tanpa kebutuhan dan keputusan tim. |
| Blaze | Pengguna meminta pemasangan. Panduan `livewire/blaze:^1.0` sudah diberikan, keberhasilan pemasangan/aktivasi belum terbukti. |
| Database | Pilihan akhir belum dikonfirmasi. Jangan menetapkan MySQL/PostgreSQL atau mengklaim database payroll tersedia. |
| Server Laravel demo | Port 8001 direncanakan agar tidak bentrok dengan mock Python port 8000. Keberhasilan membuka halaman belum dikonfirmasi. |

Folder lokal Dhaffa:
`C:\Users\Lenovo\Documents\Tugas\Tugas Kuliah\Semester 7\Proyek Perangkat Lunak\ERP-F5`.

Struktur yang telah terlihat: `web/`, `bukti/`, `dokumen/`, `examples/`, `tests/`, `openapi.yaml`, `openapi.json`, `mock_server.py`, `validate_contract.py`, `requirements-dev.txt`, dan README. Frontend serta backend berada dalam `web/`. Pengujian Laravel berada di `web/tests/`, terpisah dari pengujian Python di root.

## Kontrak aktif rekap biaya

```http
GET /api/payroll/v1/rekap-biaya?periodeId=<UUID>
Authorization: Bearer <token>
```

- Provider F-5, konsumen lintas kelompok E-2.
- Peran yang diizinkan: manajemen, petugas keuangan, dan layanan E-2 berkredensial.
- Query wajib hanya satu `periodeId`, berupa UUID. Tidak ada request body.
- Kontrak ini baru merinci rekap biaya. Endpoint daftar periode dan operasi fitur lain belum dirinci dalam paket tersebut.
- Respons berhasil memuat 17 field wajib berikut. Tabel ini ringkasan untuk orientasi, schema persis tetap mengikuti `openapi.yaml`.

| Field | Bentuk dan makna |
| --- | --- |
| `periodeId` | String UUID periode. |
| `namaPeriode` | String nama tampil. |
| `tanggalMulai` | Tanggal YYYY-MM-DD, awal periode inklusif. |
| `tanggalAkhir` | Tanggal YYYY-MM-DD, akhir periode inklusif. |
| `statusPeriode` | Draf, Dihitung, Menunggu Persetujuan, atau Disetujui. |
| `adaData` | Boolean, ada payroll atau pembayaran termin. |
| `jumlahKaryawan` | Integer jumlah karyawan dalam hasil payroll. |
| `totalPendapatan` | Angka rupiah, bonus sudah termasuk. |
| `totalBonus` | Angka rupiah, bagian dari totalPendapatan. |
| `totalPotongan` | Angka rupiah, potongan komponen di luar BPJS karyawan. |
| `totalBpjsKaryawan` | Angka rupiah, BPJS karyawan terpisah. |
| `totalGajiBersih` | Angka rupiah hasil payroll. |
| `totalBpjsPerusahaan` | Angka rupiah, ditampilkan terpisah. |
| `totalTermin` | Angka rupiah pembayaran termin dalam periode. |
| `jumlahTermin` | Integer jumlah termin unik yang pembayarannya masuk rekap. |
| `totalBiaya` | Field wajib, selalu null pada draf v0.2 sampai Q-17 disepakati. |
| `diperbaruiPada` | Timestamp ISO 8601 dengan offset +07:00. |

Semua field selain `totalBiaya` non-null. Nominal JSON berupa angka, maksimal dua desimal. Frontend memformat rupiah. Jangan menambah bonus lagi ke pendapatan, menggabungkan BPJS karyawan ke potongan komponen, atau menjumlah semua agregat sebagai total biaya.

Usulan rumus contoh gaji bersih: pendapatan dikurangi potongan komponen dan BPJS karyawan. PPh 21 masih menunggu konfirmasi Q-11, bukan kebijakan final yang boleh diasumsikan. Dasar periode pembayaran termin memakai usulan `tanggalBayar` untuk demo, final menunggu Q-12.

| Keadaan | Respons |
| --- | --- |
| Periode valid dengan data | 200, adaData true. |
| Periode valid tanpa payroll dan termin | 200, adaData false, jumlah/agregat 0, totalBiaya null. |
| Termin saja | 200, adaData true, agregat payroll 0, termin tetap terisi. |
| periodeId kosong, salah format, atau berulang | 400, INVALID_PERIODE_ID. |
| Token tidak tersedia/tidak valid | 401, UNAUTHORIZED. |
| Tidak berwenang | 403, FORBIDDEN. |
| UUID periode tidak dikenal | 404, PERIODE_NOT_FOUND. |

Error F-5 berbentuk `code`, `message`, dan `details`. Rekap tidak mengirim data pribadi atau rincian gaji per orang. Token demo dari mock hanya simulasi, bukan bukti integrasi F-1.

## Fixture yang sudah ada

| Kasus | ID periode | File |
| --- | --- | --- |
| Payroll dan termin | 01a1193d-2500-7c04-854b-8bf03c711dde | examples/success.json |
| Kosong | 01a1193d-2500-7c05-854b-8bf03c711dde | examples/success-empty.json |
| Termin saja | 01a1193d-2500-7c06-854b-8bf03c711dde | examples/success-termin-only.json |

Contoh utama memiliki pendapatan 7.750.000, bonus 750.000 yang sudah termasuk pendapatan, potongan komponen 200.000, BPJS karyawan 65.000, gaji bersih 7.485.000, BPJS perusahaan 260.000, dan termin 25.000.000. Ini angka fiktif untuk konsistensi demo, bukan data perusahaan.

## Hubungan antarkelompok

| Kelompok | Hubungan dengan F-5 | Hal yang perlu disepakati |
| --- | --- | --- |
| F-1 | Produsen autentikasi/peran | Token, akun/peran, pemetaan userId ke identitas karyawan/tenaga ahli. |
| F-2 | Produsen data karyawan dan kontrak tenaga ahli | employeeId, kontrak, expertId, referensi proyek, dan penugasan PM. |
| F-3 | Produsen rekap kehadiran/keterlambatan | Rentang periode dan arti field yang dibutuhkan payroll. |
| F-4 | Produsen status cuti | API-K04-07, GET /api/v1/leave-status. |
| E-2 | Konsumen rekap biaya F-5 | Kecukupan field, definisi totalBiaya, dan status payroll yang boleh masuk rekap. |

Tidak ada panggilan API langsung ke D-1. Q-14 tetap menanyakan apakah ID proyek dan penugasan PM tersedia dalam data kontrak F-2. Sumber data rekap adalah internal F-5, jadi demo rekap dari fixture tidak otomatis membuktikan integrasi F-2/F-3/F-4.

Kesepakatan spesifikasi F-4 yang disampaikan pengguna pada 18 Oktober 2026:

- `workingDays` adalah total hari kerja seluruh pengajuan, untuk informasi.
- `workingDaysInRange` adalah hari kerja cuti dalam rentang from sampai to, untuk bagian cuti yang masuk periode payroll.
- `paid` dan `leaveType` tetap tersedia. Makna dibayar mengikuti kebijakan yang disepakati, jangan membuat potongan berdasarkan jenis cuti sendiri.
- Batas request 31 hari dihitung inklusif.
- Contoh 400 ditambahkan untuk parameter kosong, UUID salah, dan rentang lebih dari 31 hari.
- Kontrak revisi dan request belum diverifikasi. Ini kesepakatan isi, bukan hasil uji integrasi.
- Sumber kontrak F-4: https://raw.githubusercontent.com/ComradeChandra/ppl-kelompok04-f4-cuti/main/openapi/openapi.yaml

## Pertanyaan yang tetap terbuka

- Q-12: dasar pengelompokan pembayaran termin per periode.
- Q-17: definisi total biaya, status yang masuk rekap, serta kebutuhan E-2.
- Q-14: ID proyek dan penugasan PM dalam data kontrak F-2.
- Autentikasi F-1, kredensial layanan, dan kesepakatan lintas kelas belum final.
- Link/frame Figma yang dipakai pengembang, pilihan database, serta operasi sumber pilihan periode belum dikonfirmasi dalam sesi ini.
- Pertanyaan kebijakan payroll lain pada dokumen minggu 2 tetap berlaku. Nomor Q milik F-4 tidak boleh otomatis disamakan dengan nomor Q F-5.

## Dokumen dan tenggat

- Tugas kontrak API final meminta pembaruan dokumen kontrak sebelumnya. Screenshot menunjukkan tenggat 21 Oktober 2026 pukul 08.00.
- Template laporan demo meminta satu alur lokal, kasus berhasil/gagal, API/mock, review silang, repo, commit, README, dan bukti. Tenggatnya belum dipastikan dari template.
- Dokumen minggu 2 v0.2 memakai tanggal 6 Oktober 2026 dan pengumpulan 9 Oktober 2026. Itu metadata historis, jangan diterapkan otomatis pada tugas baru.
- Reviewer dosen minggu 2: Wanda Gusdya Purnama, ST., MT., kapitalisasi normal tanpa bold.
- Pertahankan template, hindari titik koma, dan rapikan narasi Word dengan justify.
- Kolom keputusan/review resmi dosen atau mitra diisi hanya setelah ada jawaban nyata. Reviewer internal atau konsumen adalah peran berbeda, jangan otomatis menggantinya dengan dosen.
- Catatan diskusi yang sudah tidak diperlukan di dokumen final tidak perlu ditambahkan kembali.

## Cara memperbarui file ini

Perbarui hanya saat ada keputusan baru atau perubahan batas proyek. Catat tanggal, sumber konfirmasi, dan dampaknya. Pindahkan progres harian ke `PROGRESS.md`. Jangan menandai usulan sebagai kesepakatan atau tugas sebagai selesai hanya karena panduan sudah diberikan.
