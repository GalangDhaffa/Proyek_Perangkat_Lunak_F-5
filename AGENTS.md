# Pedoman kerja ERP F5

Pedoman ini berlaku untuk seluruh repository, termasuk `web/`. Ikuti instruksi pengguna dan keputusan tim terbaru yang terkonfirmasi. Kerjakan tugas yang diminta, pertahankan perubahan anggota lain, dan jangan mengklaim pekerjaan sudah selesai tanpa bukti.

## Peta acuan

| File | Isi yang menjadi acuan |
| --- | --- |
| `AGENTS.md` | Aturan kerja yang relatif tetap. |
| `PROJECT_CONTEXT.md` | Tujuan, pembagian tim, keputusan, teknologi, dan pertanyaan terbuka. |
| `PROGRESS.md` | Status pekerjaan, bukti, hambatan, dan langkah berikutnya. |
| `openapi.yaml` | Kontrak teknis request, response, tipe, dan error. |
| README | Cara menyiapkan, menjalankan, dan menguji proyek. |
| Prototype Figma yang ditunjuk tim | Acuan tampilan dan komponen UI. |

Jangan menggandakan seluruh spesifikasi atau progres di setiap file. Jika instruksi baru mengubah keputusan, perbarui acuan yang sesuai. Jika dokumen bertentangan, sebutkan perbedaannya dan selaraskan berdasarkan keputusan terbaru. File pedoman tidak membuktikan kode sudah mengimplementasikan keputusan tersebut.

## Sebelum mulai

1. Baca konteks dan status terbaru, lalu periksa bagian source code atau kontrak yang relevan dengan tugas.
2. Periksa `git status`, branch aktif, dan perubahan lokal sebelum mengedit atau mengambil pembaruan.
3. Nyatakan lingkup singkat, file yang akan disentuh, serta keputusan yang belum tersedia.
4. Lanjutkan pekerjaan yang sudah jelas. Pertanyaan terbuka pada satu bagian tidak otomatis menghentikan semua pekerjaan lain.

## Batas pekerjaan dan kolaborasi

- Fokus aktif dan pembagian anggota mengikuti `PROJECT_CONTEXT.md`. Pembagian coding terbaru adalah Rizky frontend dan Arinal backend. Jangan mengembalikan usulan pembagian sebelumnya tanpa arahan tim.
- Dua pengembang memakai satu aplikasi Laravel di `web/`. Jangan membuat aplikasi atau repository Git kedua di dalamnya.
- Koordinasikan perubahan yang menyentuh file bersama, kontrak API, atau pembagian tanggung jawab. Pertahankan kode dan perubahan lokal anggota lain.
- Kerjakan perubahan kecil yang dapat diperiksa. Jangan melakukan refactor besar, mengganti framework, atau menambah fitur di luar tugas aktif.
- Mock/fixture boleh dipakai untuk demo sesuai template. Nyatakan bagian simulasi dan batasnya, bukan mengklaim integrasi atau perhitungan nyata.

## Konsistensi API dan data

- Ikuti `openapi.yaml`, termasuk nama field, huruf besar/kecil, tipe, field wajib, null, hak akses, dan bentuk error.
- Jika kontrak berubah berdasarkan kesepakatan, selaraskan `openapi.json`, contoh respons, mock, implementasi, pengujian, dan dokumen yang terdampak. Catat versi/commit yang dipakai.
- Jangan menganggap operasi tambahan, termasuk daftar periode, sudah tersedia. Catat kebutuhan dan sepakati bentuknya sebelum membuat pasangan frontend dan backend dengan asumsi berbeda.
- Nominal JSON berupa angka rupiah. Pemformatan rupiah berada di frontend, perhitungan payroll dan agregat utama di backend.
- Bonus sudah termasuk pendapatan. Potongan komponen dan BPJS karyawan terpisah. Jangan menjumlah semua agregat untuk mendapatkan total biaya.
- Pertahankan `totalBiaya: null` sesuai kontrak aktif sampai ada perubahan yang disepakati. UI menampilkan `Belum ditetapkan`, bukan Rp0.
- Periksa validasi dan otorisasi di backend. Menyembunyikan tombol bukan pembatasan akses.
- Rekap biaya hanya memuat agregat, tanpa rincian pribadi per karyawan.
- Konsumsi data kelompok lain melalui API atau mock sesuai kontrak produsennya. Jangan mengakses database mereka secara langsung.

## Frontend

- Gunakan frame Figma terbaru yang ditentukan tim. Jika belum tersedia, lanjutkan pekerjaan nonvisual yang jelas dan minta referensi sebelum mengubah desain secara besar.
- Gunakan komponen bersama untuk sidebar, header, tombol, input, tabel, dan nominal.
- Tangani loading, hasil berisi data, kosong, dan gagal. Jangan mengarang hasil sukses setelah request gagal.
- Baca status Blade/Blaze di konteks dan progres. Jangan menganggap paket sudah terpasang hanya karena perintah instalasinya sudah diberikan.
- Simpan alamat layanan dalam konfigurasi yang sesuai. Jangan menanam kredensial layanan asli di JavaScript browser.

## Git dan dependensi

- Gunakan branch kerja masing-masing dan commit yang jelas. Ambil pembaruan setelah memastikan perubahan lokal aman, lalu gabungkan perubahan bertahap setelah diperiksa.
- Jangan memakai force push, reset destruktif, atau menghapus pekerjaan anggota lain untuk menyelesaikan konflik.
- Periksa konfigurasi, versi runtime, dan lockfile sebelum mengubah dependensi. Commit `composer.lock` dan lockfile npm yang relevan agar pemasangan tim konsisten.
- Jangan commit `.env` berisi rahasia, `.venv/`, `vendor/`, `node_modules/`, cache, atau log sensitif. Simpan contoh konfigurasi tanpa rahasia.
- Periksa `.gitignore`, perubahan diff, dan daftar staged files sebelum commit. Jangan staging massal tanpa memeriksa isinya.

## Pemeriksaan

- Jalankan perintah pada folder yang benar. Pengujian Python ada di `tests/` root, pengujian Laravel di `web/tests/`.
- Pilih pemeriksaan yang relevan: schema respons, perhitungan pembanding, status HTTP, otorisasi, build frontend, dan uji browser untuk UI.
- Untuk demo rekap, periksa data tersedia, periode kosong, termin saja, dan kasus gagal/ditolak sesuai perubahan.
- Bedakan paket terpasang, konfigurasi tersedia, aplikasi berhasil dibuka, dan alur lengkap lulus uji. Satu keadaan tidak membuktikan keadaan lainnya.
- Catat perintah dan hasil aktual. Jangan membuat screenshot, hasil lulus, tanggal review, atau hash commit fiktif.

## Dokumentasi dan penyerahan

- Pertahankan template dosen. Gunakan bahasa Indonesia yang mudah dibaca, tanpa titik koma, dan justify pada narasi Word sesuai kebutuhan.
- Review resmi, keputusan, dan revisi dosen/mitra tetap kosong sampai ada jawaban nyata. Jangan mencentang checklist dosen.
- Catat kesepakatan antarkelompok dengan tanggal, versi kontrak, temuan, dan tindak lanjut. Kesepakatan chat bukan hasil pengujian request.
- Sesudah bekerja, perbarui `PROGRESS.md` untuk status, bukti, hambatan, dan langkah berikutnya. Perbarui `PROJECT_CONTEXT.md` hanya jika keputusan berubah. Perbarui README jika cara menjalankan berubah.
- Status selesai harus memiliki bukti yang dapat diperiksa. Jika tidak diuji, tulis alasan dan batasnya.
- Saat menyerahkan perubahan, sebutkan apa yang berubah, pemeriksaan yang dijalankan, dan pekerjaan yang masih tersisa.
