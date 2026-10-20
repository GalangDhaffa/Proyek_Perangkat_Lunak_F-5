<x-layouts.app>
    <x-slot:title>Rekap Biaya - Universal ERP</x-slot:title>
    <x-slot:breadcrumb>Rekap biaya</x-slot:breadcrumb>
    <x-slot:pageTitle>Rekap biaya</x-slot:pageTitle>
    <x-slot:pageSubtitle>Tinjau biaya payroll dan pembayaran tenaga ahli per periode.</x-slot:pageSubtitle>
    <x-slot:screenCode>UI-K05-09</x-slot:screenCode>

    {{-- Filter & Periode Selector Card (Sesuai Prototype Figma UI-K05-09) --}}
    <x-ui.card class="mb-6">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                {{-- Dropdown Pilihan Periode (Menggunakan ID Fixture Resmi) --}}
                <div class="space-y-1.5">
                    <label for="select-periode" class="block text-xs font-semibold text-slate-700">
                        Periode rekap
                    </label>
                    <div class="relative">
                        <select id="select-periode" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-sm text-slate-800 focus:outline-hidden focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                            <option value="01a1193d-2500-7c04-854b-8bf03c711dde" selected>
                                Oktober 2026 (Payroll &amp; Termin)
                            </option>
                            <option value="01a1193d-2500-7c05-854b-8bf03c711dde">
                                Periode kosong (Tanpa Data)
                            </option>
                            <option value="01a1193d-2500-7c06-854b-8bf03c711dde">
                                Termin saja (Hanya Termin)
                            </option>
                            <option disabled>────────── Skenario Uji Galat ──────────</option>
                            <option value="00000000-0000-0000-0000-000000000000">
                                Simulasi 404 - Periode Tidak Dikenal
                            </option>
                            <option value="invalid-uuid-sample">
                                Simulasi 400 - Format UUID Salah
                            </option>
                            <option value="custom">Input UUID Manual...</option>
                        </select>
                    </div>
                </div>

                {{-- Input UUID Manual (Kondisional) --}}
                <div id="input-custom-container" class="space-y-1.5 hidden">
                    <label for="input-custom-uuid" class="block text-xs font-semibold text-slate-700">
                        UUID Periode Manual
                    </label>
                    <input
                        type="text"
                        id="input-custom-uuid"
                        placeholder="8-4-4-4-12 UUID"
                        class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-sm text-slate-800 font-mono focus:outline-hidden focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                </div>

                {{-- Pilihan Peran & Simulasi Autentikasi (Kontrak API-K05-01) --}}
                <div class="space-y-1.5">
                    <label for="select-auth-role" class="block text-xs font-semibold text-slate-700">
                        Hak akses pemanggil
                    </label>
                    <select id="select-auth-role" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-sm text-slate-800 focus:outline-hidden focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                        <option value="manajemen" selected>Manajemen (Bearer mock-manajemen)</option>
                        <option value="petugas_keuangan">Petugas Keuangan (Bearer mock-keuangan)</option>
                        <option value="layanan_e2">Layanan E-2 (Bearer mock-e2)</option>
                        <option value="unauthorized">Simulasi 401 - Token invalid (kirim ke backend)</option>
                        <option value="forbidden">Simulasi 403 - Bearer mock-karyawan (kirim ke backend)</option>
                    </select>
                </div>
            </div>

            {{-- Tombol Aksi Tampilkan Rekap (Sesuai Tombol Figma Outline) --}}
            <div class="flex items-center gap-2 shrink-0">
                <x-ui.button variant="outline" id="btn-tampilkan-rekap" class="font-medium text-slate-700 border-slate-300 hover:bg-slate-50">
                    Tampilkan rekap
                </x-ui.button>
            </div>
        </div>

        {{-- Indikator Status Transparan Sumber Data --}}
        <div class="mt-4 pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between text-xs text-slate-500 gap-2">
            <div class="flex items-center gap-2">
                <span>Status koneksi:</span>
                <span id="badge-source" class="text-xs font-medium px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 border border-slate-200">
                    Menghubungkan...
                </span>
            </div>
            <div class="text-[11px] text-slate-400">
                Semua request dikirim ke backend Laravel. Backend menggunakan fixture demo, bukan data nyata.
            </div>
        </div>
    </x-ui.card>

    {{-- State 1: Loading Indicator --}}
    <div id="state-loading" class="hidden py-12 text-center">
        <div class="inline-flex items-center gap-3 px-5 py-3 rounded-xl bg-white border border-slate-200 shadow-xs">
            <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-sm font-medium text-slate-700">Memuat data rekap biaya periode...</span>
        </div>
    </div>

    {{-- State 2: Error Alert Box (Menangani Kasus Gagal 400, 401, 403, 404 Sesuai Kontrak) --}}
    <div id="state-error" class="hidden mb-6">
        <div class="p-5 bg-rose-50/80 border border-rose-200 rounded-xl">
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-lg bg-rose-100 text-rose-700 flex items-center justify-center shrink-0 font-bold text-sm">
                    !
                </div>
                <div class="flex-1">
                    <div class="flex items-center gap-2">
                        <span id="error-code" class="font-mono text-xs font-bold text-rose-800 px-2 py-0.5 rounded bg-rose-100">
                            ERROR_CODE
                        </span>
                        <span id="error-http-status" class="text-xs font-medium text-rose-600">
                            HTTP Status
                        </span>
                    </div>
                    <p id="error-message" class="text-sm font-medium text-rose-900 mt-1.5">
                        Pesan kesalahan akan ditampilkan di sini.
                    </p>
                    <ul id="error-details" class="mt-2 space-y-1 list-disc list-inside">
                        {{-- Rincian field jika ada --}}
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- State 3: Konten Utama Rekap Biaya --}}
    <div id="state-content" class="space-y-6">

        {{-- Banner Kasus Periode Kosong (adaData: false) --}}
        <div id="state-empty-notice" class="hidden p-4 bg-amber-50/80 border border-amber-200 rounded-xl">
            <div class="flex items-center gap-3 text-amber-900">
                <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="text-sm">
                    <span class="font-semibold">Periode Tanpa Transaksi (adaData = false):</span>
                    Periode ini valid tetapi belum memuat hasil payroll maupun pembayaran termin. Seluruh nominal bernilai 0.
                </div>
            </div>
        </div>

        {{-- Periode Header Summary Card --}}
        <div class="bg-white border border-slate-200/90 rounded-xl p-6 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h2 id="view-nama-periode" class="text-xl font-bold text-slate-900 tracking-tight">
                        Oktober 2026 (contoh fiktif)
                    </h2>
                    <span id="view-status-periode" class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        Disetujui
                    </span>
                </div>
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500 mt-2">
                    <div class="flex items-center gap-1">
                        <span class="text-slate-400">Rentang:</span>
                        <span id="view-tanggal-rentang" class="font-medium text-slate-700">-</span>
                    </div>
                    <span>&bull;</span>
                    <div class="flex items-center gap-1">
                        <span class="text-slate-400">Diperbarui:</span>
                        <span id="view-diperbarui-pada" class="font-medium text-slate-700">-</span>
                    </div>
                </div>
            </div>
            <div class="text-xs font-mono text-slate-400 bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-100">
                ID: <span id="view-periode-id" class="text-slate-600">01a1193d-2500-7c04-854b-8bf03c711dde</span>
            </div>
        </div>

        {{-- 3 Kartu Ringkasan Metrik (Persis Sesuai Figma UI-K05-09) --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- Stat 1: Pendapatan --}}
            <x-ui.stat-card
                label="Pendapatan"
                value="Rp0"
                id="stat-pendapatan"
                subtext="Termasuk bonus (tidak ditambah ulang)"
            />

            {{-- Stat 2: Potongan Karyawan --}}
            <x-ui.stat-card
                label="Potongan karyawan"
                value="Rp0"
                id="stat-potongan"
                subtext="Potongan komponen di luar BPJS"
            />

            {{-- Stat 3: Gaji Bersih --}}
            <x-ui.stat-card
                label="Gaji bersih"
                value="Rp0"
                id="stat-gajibersih"
                subtext="Hasil bersih payroll karyawan"
            />
        </div>

        {{-- Card Rincian Biaya (Persis Sesuai Tabel Figma UI-K05-09 & 17 Field Schema RekapBiaya) --}}
        <x-ui.card>
            <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
                <h3 class="text-base font-semibold text-slate-800 tracking-tight">Rincian biaya</h3>
                <span class="text-xs text-slate-400">Agregat resmi F-5 per periode</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-200/80 text-xs font-semibold text-slate-500">
                            <th scope="col" class="pb-3 font-medium">Komponen</th>
                            <th scope="col" class="pb-3 font-medium text-right pr-6">Nilai</th>
                            <th scope="col" class="pb-3 font-medium">Sumber</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        {{-- Row 1: Pendapatan (termasuk bonus) --}}
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 font-medium text-slate-800">
                                Pendapatan (termasuk bonus)
                            </td>
                            <td id="row-pendapatan" class="py-3.5 text-right font-semibold text-slate-900 pr-6">
                                Rp0
                            </td>
                            <td id="row-pendapatan-sumber" class="py-3.5 text-xs text-slate-500">
                                Hasil payroll
                            </td>
                        </tr>

                        {{-- Row 2: Bonus Periode (Rincian dalam Pendapatan) --}}
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 pl-4 text-slate-600 text-xs flex items-center gap-1.5">
                                <span class="text-slate-300">&boxur;</span>
                                <span>Bonus periode (bagian dari pendapatan)</span>
                            </td>
                            <td id="row-bonus" class="py-3.5 text-right text-xs font-medium text-slate-700 pr-6">
                                Rp0
                            </td>
                            <td class="py-3.5 text-xs text-slate-400">
                                Rincian komponen bonus
                            </td>
                        </tr>

                        {{-- Row 3: Potongan Karyawan --}}
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 font-medium text-slate-800">
                                Potongan komponen
                            </td>
                            <td id="row-potongan" class="py-3.5 text-right font-semibold text-slate-900 pr-6">
                                Rp0
                            </td>
                            <td class="py-3.5 text-xs text-slate-500">
                                Rincian potongan karyawan di luar BPJS
                            </td>
                        </tr>

                        {{-- Row 4: BPJS Karyawan --}}
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 pl-4 text-slate-600 text-xs flex items-center gap-1.5">
                                <span class="text-slate-300">&boxur;</span>
                                <span>BPJS porsi karyawan (terpisah)</span>
                            </td>
                            <td id="row-bpjs-karyawan" class="py-3.5 text-right text-xs font-medium text-slate-700 pr-6">
                                Rp0
                            </td>
                            <td class="py-3.5 text-xs text-slate-400">
                                Hasil payroll karyawan
                            </td>
                        </tr>

                        {{-- Row 5: Gaji Bersih --}}
                        <tr class="hover:bg-slate-50/60 transition-colors bg-blue-50/20">
                            <td class="py-3.5 font-medium text-slate-800">
                                Gaji bersih payroll
                            </td>
                            <td id="row-gaji-bersih" class="py-3.5 text-right font-bold text-blue-700 pr-6">
                                Rp0
                            </td>
                            <td class="py-3.5 text-xs text-slate-500">
                                Pendapatan &minus; potongan &minus; BPJS karyawan
                            </td>
                        </tr>

                        {{-- Row 6: BPJS Perusahaan --}}
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 font-medium text-slate-800">
                                BPJS perusahaan
                            </td>
                            <td id="row-bpjs-perusahaan" class="py-3.5 text-right font-semibold text-slate-900 pr-6">
                                Rp0
                            </td>
                            <td class="py-3.5 text-xs text-slate-500">
                                Aturan BPJS contoh (ditanggung terpisah)
                            </td>
                        </tr>

                        {{-- Row 7: Termin Tenaga Ahli --}}
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 font-medium text-slate-800">
                                Termin tenaga ahli
                            </td>
                            <td id="row-termin" class="py-3.5 text-right font-semibold text-slate-900 pr-6">
                                Rp0
                            </td>
                            <td id="row-termin-sumber" class="py-3.5 text-xs text-slate-500">
                                Kontrak contoh &middot; Terpisah
                            </td>
                        </tr>

                        {{-- Row 8: Total Biaya (Sesuai Aturan AGENTS.md: Tampilkan "Belum ditetapkan" bukan Rp0) --}}
                        <tr class="border-t-2 border-slate-200 bg-slate-50/70">
                            <td class="py-4 font-bold text-slate-900">
                                Total biaya
                            </td>
                            <td id="row-total-biaya" class="py-4 text-right pr-6 italic text-slate-500 font-medium">
                                Belum ditetapkan
                            </td>
                            <td class="py-4 text-xs text-slate-500">
                                Sesuai kontrak aktif draf v0.2 (Menunggu kesepakatan Q-17)
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </x-ui.card>

        {{-- Metadata Tambahan (Melengkapi 17 Field Schema RekapBiaya OpenAPI) --}}
        <div class="bg-white border border-slate-200/80 rounded-xl p-5 text-xs text-slate-600 shadow-xs">
            <div class="font-semibold text-slate-700 uppercase tracking-wider text-[11px] mb-3">
                Kesiapan Data &amp; Metadata (17 Field OpenAPI)
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <span class="text-slate-400 block mb-0.5">Status data (adaData):</span>
                    <span id="meta-ada-data" class="font-semibold text-slate-800">Tersedia</span>
                </div>
                <div>
                    <span class="text-slate-400 block mb-0.5">Jumlah karyawan:</span>
                    <span id="meta-jumlah-karyawan" class="font-semibold text-slate-800">1 orang</span>
                </div>
                <div>
                    <span class="text-slate-400 block mb-0.5">Jumlah termin:</span>
                    <span id="meta-jumlah-termin" class="font-semibold text-slate-800">1 termin</span>
                </div>
                <div>
                    <span class="text-slate-400 block mb-0.5">Total biaya formula:</span>
                    <span class="font-semibold text-amber-700">null (Q-17 Terbuka)</span>
                </div>
            </div>
        </div>

    </div>
</x-layouts.app>
