<x-layouts.app>
    <x-slot:title>Rekap Biaya - Universal ERP</x-slot:title>
    <x-slot:breadcrumb>Rekap biaya</x-slot:breadcrumb>
    <x-slot:pageTitle>Rekap biaya</x-slot:pageTitle>
    <x-slot:pageSubtitle>Tinjau total biaya payroll dan pembayaran tenaga ahli per periode.</x-slot:pageSubtitle>

    {{-- Kartu Pilihan Periode --}}
    <x-ui.card class="mb-6">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">

            {{-- Dropdown Periode --}}
            <div class="space-y-1.5">
                <label for="select-periode" class="block text-xs font-semibold text-slate-700">
                    Periode rekap
                </label>
                <select id="select-periode" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-sm text-slate-800 focus:outline-hidden focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors min-w-56">
                    <option value="01a1193d-2500-7c04-854b-8bf03c711dde" selected>
                        Oktober 2026 — Payroll &amp; Termin
                    </option>
                    <option value="01a1193d-2500-7c05-854b-8bf03c711dde">
                        Oktober 2026 — Belum ada transaksi
                    </option>
                    <option value="01a1193d-2500-7c06-854b-8bf03c711dde">
                        Oktober 2026 — Termin saja
                    </option>
                </select>
            </div>

            {{-- Tombol Tampilkan --}}
            <div class="flex items-center gap-3 shrink-0">
                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-amber-700 bg-amber-50 border border-amber-200 px-2.5 py-1 rounded-full">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                    Data demo
                </span>
                <x-ui.button variant="outline" id="btn-tampilkan-rekap" class="font-medium text-slate-700 border-slate-300 hover:bg-slate-50">
                    Tampilkan rekap
                </x-ui.button>
            </div>
        </div>

        {{-- Panel Pengujian (tersembunyi secara default) --}}
        <div class="mt-4 pt-3 border-t border-slate-100">
            <button
                id="btn-toggle-panel-uji"
                type="button"
                class="flex items-center gap-1.5 text-xs text-slate-400 hover:text-slate-600 transition-colors"
                aria-expanded="false"
                aria-controls="panel-pengujian"
            >
                <svg id="icon-toggle-uji" class="w-3.5 h-3.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <span>Pengujian demo</span>
            </button>

            <div id="panel-pengujian" class="hidden mt-3">
                <div class="p-4 bg-slate-50 border border-slate-200 rounded-lg space-y-4">
                    <p class="text-[11px] text-slate-400">
                        Kontrol di bawah ini hanya tersedia pada mode demo. Semua request dikirim ke backend dan terlihat di Network browser.
                    </p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

                        {{-- Pilihan peran / token --}}
                        <div class="space-y-1.5">
                            <label for="select-auth-role" class="block text-xs font-semibold text-slate-600">
                                Simulasi peran pemanggil
                            </label>
                            <select id="select-auth-role" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-sm text-slate-800 focus:outline-hidden focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                <option value="manajemen" selected>Manajemen</option>
                                <option value="petugas_keuangan">Petugas Keuangan</option>
                                <option value="layanan_e2">Layanan E-2</option>
                                <option value="unauthorized">Simulasi 401 — token tidak valid</option>
                                <option value="forbidden">Simulasi 403 — peran tanpa izin</option>
                            </select>
                        </div>

                        {{-- Skenario galat periode --}}
                        <div class="space-y-1.5">
                            <label for="select-skenario-galat" class="block text-xs font-semibold text-slate-600">
                                Skenario periode uji
                            </label>
                            <select id="select-skenario-galat" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-sm text-slate-800 focus:outline-hidden focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                <option value="" selected>— Gunakan pilihan periode di atas —</option>
                                <option value="00000000-0000-0000-0000-000000000000">404 — UUID tidak dikenal</option>
                                <option value="invalid-uuid-sample">400 — Format UUID salah</option>
                                <option value="custom">Input UUID manual…</option>
                            </select>
                        </div>

                        {{-- Input UUID Manual --}}
                        <div id="input-custom-container" class="space-y-1.5 hidden">
                            <label for="input-custom-uuid" class="block text-xs font-semibold text-slate-600">
                                UUID periode manual
                            </label>
                            <input
                                type="text"
                                id="input-custom-uuid"
                                placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"
                                class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-sm text-slate-800 font-mono focus:outline-hidden focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-ui.card>

    {{-- State: Memuat --}}
    <div id="state-loading" class="hidden py-12 text-center">
        <div class="inline-flex items-center gap-3 px-5 py-3 rounded-xl bg-white border border-slate-200 shadow-xs">
            <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-sm font-medium text-slate-700">Memuat rekap…</span>
        </div>
    </div>

    {{-- State: Error --}}
    <div id="state-error" class="hidden mb-6">
        <div class="p-5 bg-rose-50/80 border border-rose-200 rounded-xl">
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-lg bg-rose-100 text-rose-700 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <span id="error-code" class="font-mono text-xs font-bold text-rose-800 px-2 py-0.5 rounded bg-rose-100">
                            ERROR_CODE
                        </span>
                        <span id="error-http-status" class="text-xs font-medium text-rose-600">
                            —
                        </span>
                    </div>
                    <p id="error-message" class="text-sm font-medium text-rose-900 mt-1.5">
                        Terjadi kesalahan pada permintaan rekap.
                    </p>
                    <ul id="error-details" class="mt-2 space-y-1 list-disc list-inside hidden">
                    </ul>
                    {{-- Tombol coba lagi untuk error koneksi --}}
                    <button
                        id="btn-retry"
                        type="button"
                        class="hidden mt-3 text-xs font-semibold text-rose-700 underline hover:text-rose-900 transition-colors"
                    >
                        Coba lagi
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- State: Konten Rekap Biaya --}}
    <div id="state-content" class="space-y-6">

        {{-- Banner Periode Tanpa Transaksi --}}
        <div id="state-empty-notice" class="hidden p-4 bg-amber-50/80 border border-amber-200 rounded-xl">
            <div class="flex items-center gap-3 text-amber-900">
                <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-sm">Belum ada transaksi pada periode ini.</p>
            </div>
        </div>

        {{-- Header Periode --}}
        <div class="bg-white border border-slate-200/90 rounded-xl p-6 shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <div class="flex flex-wrap items-center gap-2.5">
                        <h2 id="view-nama-periode" class="text-xl font-bold text-slate-900 tracking-tight">
                            —
                        </h2>
                        <span id="view-status-periode" class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                            —
                        </span>
                    </div>
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500 mt-2">
                        <div class="flex items-center gap-1">
                            <span class="text-slate-400">Rentang:</span>
                            <span id="view-tanggal-rentang" class="font-medium text-slate-700">—</span>
                        </div>
                        <span>&bull;</span>
                        <div class="flex items-center gap-1">
                            <span class="text-slate-400">Diperbarui:</span>
                            <span id="view-diperbarui-pada" class="font-medium text-slate-700">—</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 3 Kartu Ringkasan --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <x-ui.stat-card
                label="Pendapatan"
                value="—"
                id="stat-pendapatan"
                subtext="Termasuk bonus periode"
            />
            <x-ui.stat-card
                label="Potongan karyawan"
                value="—"
                id="stat-potongan"
                subtext="Di luar BPJS karyawan"
            />
            <x-ui.stat-card
                label="Gaji bersih"
                value="—"
                id="stat-gajibersih"
                subtext="Hasil bersih payroll"
            />
        </div>

        {{-- Tabel Rincian Biaya --}}
        <x-ui.card>
            <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
                <h3 class="text-base font-semibold text-slate-800 tracking-tight">Rincian biaya</h3>
                <span id="view-ringkasan-karyawan-termin" class="text-xs text-slate-400">—</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-200/80 text-xs font-semibold text-slate-500">
                            <th scope="col" class="pb-3 font-medium">Komponen</th>
                            <th scope="col" class="pb-3 font-medium text-right pr-6">Nilai</th>
                            <th scope="col" class="pb-3 font-medium">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">

                        {{-- Pendapatan --}}
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 font-medium text-slate-800">
                                Pendapatan
                            </td>
                            <td id="row-pendapatan" class="py-3.5 text-right font-semibold text-slate-900 pr-6">
                                —
                            </td>
                            <td class="py-3.5 text-xs text-slate-500">
                                Termasuk bonus periode
                            </td>
                        </tr>

                        {{-- Bonus (rincian dalam Pendapatan) --}}
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3 pl-5 text-slate-500 text-xs flex items-center gap-1.5">
                                <span class="text-slate-300">&boxur;</span>
                                <span>Bonus periode</span>
                            </td>
                            <td id="row-bonus" class="py-3 text-right text-xs font-medium text-slate-600 pr-6">
                                —
                            </td>
                            <td class="py-3 text-xs text-slate-400">
                                Sudah termasuk dalam pendapatan
                            </td>
                        </tr>

                        {{-- Potongan Komponen --}}
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 font-medium text-slate-800">
                                Potongan komponen
                            </td>
                            <td id="row-potongan" class="py-3.5 text-right font-semibold text-slate-900 pr-6">
                                —
                            </td>
                            <td class="py-3.5 text-xs text-slate-500">
                                Di luar BPJS karyawan
                            </td>
                        </tr>

                        {{-- BPJS Karyawan --}}
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3 pl-5 text-slate-500 text-xs flex items-center gap-1.5">
                                <span class="text-slate-300">&boxur;</span>
                                <span>BPJS karyawan</span>
                            </td>
                            <td id="row-bpjs-karyawan" class="py-3 text-right text-xs font-medium text-slate-600 pr-6">
                                —
                            </td>
                            <td class="py-3 text-xs text-slate-400">
                                Dicatat terpisah dari potongan komponen
                            </td>
                        </tr>

                        {{-- Gaji Bersih --}}
                        <tr class="hover:bg-slate-50/60 transition-colors bg-blue-50/20">
                            <td class="py-3.5 font-medium text-slate-800">
                                Gaji bersih payroll
                            </td>
                            <td id="row-gaji-bersih" class="py-3.5 text-right font-bold text-blue-700 pr-6">
                                —
                            </td>
                            <td class="py-3.5 text-xs text-slate-500">
                                Pendapatan &minus; potongan &minus; BPJS karyawan
                            </td>
                        </tr>

                        {{-- BPJS Perusahaan --}}
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 font-medium text-slate-800">
                                BPJS perusahaan
                            </td>
                            <td id="row-bpjs-perusahaan" class="py-3.5 text-right font-semibold text-slate-900 pr-6">
                                —
                            </td>
                            <td class="py-3.5 text-xs text-slate-500">
                                Ditanggung perusahaan, terpisah dari gaji
                            </td>
                        </tr>

                        {{-- Termin Tenaga Ahli --}}
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 font-medium text-slate-800">
                                Termin tenaga ahli
                            </td>
                            <td id="row-termin" class="py-3.5 text-right font-semibold text-slate-900 pr-6">
                                —
                            </td>
                            <td id="row-termin-keterangan" class="py-3.5 text-xs text-slate-500">
                                Pembayaran termin periode ini
                            </td>
                        </tr>

                        {{-- Total Biaya --}}
                        <tr class="border-t-2 border-slate-200 bg-slate-50/70">
                            <td class="py-4 font-bold text-slate-900">
                                Total biaya
                            </td>
                            <td id="row-total-biaya" class="py-4 text-right pr-6 italic text-slate-400 font-medium">
                                Belum ditetapkan
                            </td>
                            <td class="py-4 text-xs text-slate-400">
                                Definisi total biaya masih menunggu kesepakatan
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </x-ui.card>

    </div>
</x-layouts.app>
