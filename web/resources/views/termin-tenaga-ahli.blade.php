<x-layouts.app>
    <x-slot:title>Termin Tenaga Ahli - Universal ERP</x-slot:title>
    <x-slot:sessionUser>Petugas payroll &middot; Sesi demo</x-slot:sessionUser>
    <x-slot:breadcrumb>Termin tenaga ahli</x-slot:breadcrumb>
    <x-slot:pageTitle>Termin tenaga ahli</x-slot:pageTitle>
    <x-slot:pageSubtitle>Kelola daftar termin, verifikasi milestone deliverable tenaga ahli, dan catat realisasi pembayaran per periode.</x-slot:pageSubtitle>

    <x-slot:headerActions>
        <div class="flex flex-wrap items-center gap-2">
            <span class="inline-flex items-center gap-1.5 text-xs font-medium text-amber-700 bg-amber-50 border border-amber-200 px-2.5 py-1 rounded-full">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                Mode demo
            </span>
            <a
                id="btn-nav-ke-periode"
                href="{{ url('/periode-payroll') }}"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 hover:text-blue-600 transition-colors shadow-2xs"
                title="Buka halaman Periode Payroll"
            >
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span>Periode payroll</span>
            </a>
            <a
                id="btn-nav-ke-rekap"
                href="{{ url('/rekap-biaya') }}"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 hover:text-blue-600 transition-colors shadow-2xs"
                title="Lihat Rekap Biaya"
            >
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <span>Rekap biaya</span>
            </a>
            <button
                type="button"
                id="btn-buka-tambah-termin"
                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 active:bg-blue-800 transition-colors shadow-xs"
            >
                <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Tambah termin</span>
            </button>
        </div>
    </x-slot:headerActions>

    <div
        id="termin-tenaga-ahli-app"
        class="space-y-6"
        data-initial-periods='@json($periods ?? [])'
        data-selected-periode-id="{{ $selectedPeriodeId ?? '' }}"
    >

        {{-- Banner Alert Sukses --}}
        <div
            id="alert-sukses"
            class="hidden p-4 text-sm font-medium text-emerald-800 bg-emerald-50 border border-emerald-200 rounded-xl items-center gap-3 transition-all"
            role="status"
            aria-live="polite"
        >
            <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <div class="flex-1">
                <div class="font-semibold text-emerald-900">Operasi Berhasil</div>
                <div id="text-sukses" class="text-xs text-emerald-700 mt-0.5">Data termin tenaga ahli berhasil diperbarui.</div>
            </div>
            <button type="button" id="btn-close-alert-sukses" class="text-emerald-500 hover:text-emerald-700 text-xs font-bold px-2 py-1">
                &times;
            </button>
        </div>

        {{-- Banner Alert Galat --}}
        <div
            id="alert-error"
            class="hidden p-4 text-sm font-medium text-rose-800 bg-rose-50 border border-rose-200 rounded-xl items-start gap-3 transition-all"
            role="alert"
        >
            <div class="w-8 h-8 rounded-lg bg-rose-100 text-rose-700 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="flex-1">
                <div class="font-semibold text-rose-900" id="title-error">Terjadi Kesalahan</div>
                <div id="text-error" class="text-xs text-rose-700 mt-0.5">Silakan periksa kembali formulir atau coba beberapa saat lagi.</div>
                <div class="mt-3 flex items-center gap-2" id="wrapper-retry-error">
                    <button
                        type="button"
                        id="btn-retry-error"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-rose-600 rounded-lg hover:bg-rose-700 transition-colors shadow-2xs"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        <span>Coba lagi</span>
                    </button>
                    <button type="button" id="btn-close-alert-error" class="text-xs font-medium text-rose-600 hover:text-rose-800 px-2 py-1">
                        Tutup
                    </button>
                </div>
            </div>
        </div>

        {{-- Banner Informasi Demo Adapter --}}
        <div class="p-4 bg-blue-50/70 border border-blue-200 rounded-xl text-xs text-blue-900 flex items-start gap-3">
            <svg class="w-4 h-4 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="space-y-1">
                <div class="font-semibold text-blue-950">Adapter Demo Terpisah (UI-K05-08)</div>
                <p class="text-blue-800/90 leading-relaxed">
                    Kontrak API backend untuk termin tenaga ahli belum tersedia. Data pada halaman ini disimpan dalam adapter demo terpisah browser dan disimulasikan sesuai aturan ERP F-5. Data bukan data nyata dari modul F-2 dan tidak mengeksekusi pencairan perbankan asli.
                </p>
            </div>
        </div>

        {{-- Kartu Filter & Pemilihan Periode --}}
        <x-ui.card>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                {{-- Selector Periode --}}
                <div class="space-y-1.5">
                    <label for="select-periode" class="block text-xs font-semibold text-slate-700">
                        Periode payroll
                    </label>
                    <select
                        id="select-periode"
                        class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-sm text-slate-800 focus:outline-hidden focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors shadow-2xs cursor-pointer"
                    >
                        <option value="semua">Semua Periode</option>
                        @forelse($periods ?? [] as $p)
                            @php
                                $pUuid = $p->uuid ?: $p->id;
                                $pNama = $p->nama_periode ?: \App\Models\PayrollPeriod::generateNamaPeriode($p->tanggal_mulai);
                                $isCurrentSelected = ($selectedPeriodeId && strtolower($selectedPeriodeId) === strtolower($pUuid));
                            @endphp
                            <option value="{{ $pUuid }}" {{ $isCurrentSelected ? 'selected' : '' }}>
                                {{ $pNama }} ({{ $p->status ?? 'Draf' }})
                            </option>
                        @empty
                            <option value="01a1193d-2500-7c04-854b-8bf03c711dde" selected>November 2026 (Demo)</option>
                        @endforelse
                    </select>
                    <p id="info-periode-rentang" class="text-[11px] text-slate-500 flex items-center gap-1.5 pt-1">
                        <span class="text-slate-400">Rentang:</span>
                        <span id="text-periode-rentang" class="font-medium text-slate-700">&mdash;</span>
                    </p>
                </div>

                {{-- Filter Status Termin --}}
                <div class="space-y-1.5">
                    <label for="select-filter-status" class="block text-xs font-semibold text-slate-700">
                        Filter status termin
                    </label>
                    <select
                        id="select-filter-status"
                        class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-sm text-slate-800 focus:outline-hidden focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors shadow-2xs cursor-pointer"
                    >
                        <option value="semua" selected>Semua Status</option>
                        <option value="Draf">Draf</option>
                        <option value="Menunggu Verifikasi PM">Menunggu Verifikasi PM</option>
                        <option value="Disetujui PM">Disetujui PM</option>
                        <option value="Dibayar">Dibayar</option>
                    </select>
                    <p class="text-[11px] text-slate-400 pt-1">Saring daftar berdasarkan status pembayaran termin.</p>
                </div>

                {{-- Pencarian Cepat --}}
                <div class="space-y-1.5">
                    <label for="input-search" class="block text-xs font-semibold text-slate-700">
                        Cari termin / tenaga ahli
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </span>
                        <input
                            type="text"
                            id="input-search"
                            placeholder="Ketik nama, kode, atau kontrak…"
                            class="w-full bg-white border border-slate-300 rounded-lg pl-9 pr-3 py-2 text-sm text-slate-800 placeholder-slate-400 transition-colors focus:outline-hidden focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-2xs"
                        >
                    </div>
                    <p class="text-[11px] text-slate-400 pt-1">Cari instan berdasarkan nama ahli, judul, atau kontrak.</p>
                </div>

            </div>

            {{-- Accordion Pengujian Demo --}}
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
                    <span>Pengujian skenario demo (Kondisi UI Termin)</span>
                </button>

                <div id="panel-pengujian" class="hidden mt-3">
                    <div class="p-4 bg-slate-50 border border-slate-200 rounded-lg space-y-3">
                        <p class="text-[11px] text-slate-500">
                            Pilih skenario di bawah ini untuk menguji seluruh kondisi antarmuka termin tenaga ahli: memuat, data lengkap, kondisi kosong, validasi, dan penanganan galat.
                        </p>
                        <div class="flex flex-wrap items-center gap-2">
                            <button
                                type="button"
                                id="btn-uji-skenario-normal"
                                class="px-2.5 py-1 text-xs font-medium rounded-md bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 transition-colors shadow-2xs"
                            >
                                Skenario Normal (3 Termin Bawaan)
                            </button>
                            <button
                                type="button"
                                id="btn-uji-skenario-kosong"
                                class="px-2.5 py-1 text-xs font-medium rounded-md bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 transition-colors shadow-2xs"
                            >
                                Skenario Kosong (0 Termin)
                            </button>
                            <button
                                type="button"
                                id="btn-uji-simulasi-loading"
                                class="px-2.5 py-1 text-xs font-medium rounded-md bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 transition-colors shadow-2xs"
                            >
                                Simulasi Memuat (Loading State)
                            </button>
                            <button
                                type="button"
                                id="btn-uji-simulasi-error"
                                class="px-2.5 py-1 text-xs font-medium rounded-md bg-rose-50 border border-rose-200 text-rose-700 hover:bg-rose-100 transition-colors shadow-2xs"
                            >
                                Simulasi Galat Server (500)
                            </button>
                            <button
                                type="button"
                                id="btn-uji-reset-demo"
                                class="px-2.5 py-1 text-xs font-medium rounded-md bg-amber-50 border border-amber-200 text-amber-800 hover:bg-amber-100 transition-colors shadow-2xs"
                            >
                                Reset Data Demo ke Bawaan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </x-ui.card>

        {{-- Ringkasan Statistik (KPI Cards) --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Total Termin Periode --}}
            <div class="bg-white p-5 rounded-xl border border-slate-200/90 shadow-2xs">
                <div class="text-xs font-medium text-slate-500">Total Nominal Termin</div>
                <div id="stat-total-termin" class="text-xl font-bold text-slate-900 font-mono mt-1.5">Rp 0</div>
                <div class="text-[11px] text-slate-400 mt-1">Akumulasi termin pada filter aktif</div>
            </div>

            {{-- Jumlah Termin --}}
            <div class="bg-white p-5 rounded-xl border border-slate-200/90 shadow-2xs">
                <div class="text-xs font-medium text-slate-500">Jumlah Kontrak / Termin</div>
                <div id="stat-jumlah-termin" class="text-xl font-bold text-blue-700 font-mono mt-1.5">0</div>
                <div class="text-[11px] text-slate-400 mt-1">Total berkas termin tercatat</div>
            </div>

            {{-- Termin Dibayar --}}
            <div class="bg-white p-5 rounded-xl border border-slate-200/90 shadow-2xs">
                <div class="text-xs font-medium text-slate-500">Termin Sudah Dibayar</div>
                <div id="stat-termin-dibayar" class="text-xl font-bold text-emerald-700 font-mono mt-1.5">Rp 0</div>
                <div id="stat-termin-dibayar-count" class="text-[11px] text-emerald-600 mt-1 font-medium">0 termin terealisasi</div>
            </div>

            {{-- Menunggu Verifikasi --}}
            <div class="bg-white p-5 rounded-xl border border-slate-200/90 shadow-2xs">
                <div class="text-xs font-medium text-slate-500">Menunggu Verifikasi / Draf</div>
                <div id="stat-termin-menunggu" class="text-xl font-bold text-amber-700 font-mono mt-1.5">Rp 0</div>
                <div id="stat-termin-menunggu-count" class="text-[11px] text-amber-600 mt-1 font-medium">0 termin dalam antrean</div>
            </div>
        </div>

        {{-- State: Memuat (Loading State) --}}
        <div id="state-loading" class="hidden py-14 text-center">
            <div class="inline-flex items-center gap-3 px-6 py-3.5 rounded-xl bg-white border border-slate-200 shadow-xs">
                <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <div class="text-left">
                    <div class="text-sm font-semibold text-slate-800">Memuat data termin tenaga ahli…</div>
                    <div class="text-[11px] text-slate-400">Sinkronisasi data adapter demo bersama</div>
                </div>
            </div>
        </div>

        {{-- State: Kosong (Empty State) --}}
        <div id="state-empty" class="hidden p-10 bg-white rounded-xl border border-dashed border-slate-300 text-center space-y-3">
            <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-base font-semibold text-slate-800">Tidak Ada Termin Tenaga Ahli</h3>
                <p class="text-xs text-slate-500 mt-1 max-w-md mx-auto">
                    Belum ada data pembayaran termin tenaga ahli yang tercatat untuk periode atau kriteria pencarian ini.
                </p>
            </div>
            <div class="flex items-center justify-center gap-3 pt-2">
                <button
                    type="button"
                    id="btn-empty-tambah"
                    class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors shadow-2xs"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Tambah termin baru</span>
                </button>
                <button
                    type="button"
                    id="btn-empty-muat-demo"
                    class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors shadow-2xs"
                >
                    <span>Muat contoh data</span>
                </button>
            </div>
        </div>

        {{-- State: Konten Tabel Termin --}}
        <div id="state-content" class="bg-white rounded-xl border border-slate-200/90 shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-semibold text-slate-900 tracking-tight">
                        Daftar termin tenaga ahli
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Daftar jadwal dan realisasi pembayaran milestone tenaga ahli konsultan.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <span id="badge-total-baris" class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-slate-100 text-slate-700">
                        0 data
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-700" id="table-termin">
                    <thead class="bg-slate-50/75 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200/80">
                        <tr>
                            <th scope="col" class="px-4 py-3 min-w-[200px]">Kode &amp; Nama Termin</th>
                            <th scope="col" class="px-4 py-3 min-w-[180px]">Tenaga Ahli</th>
                            <th scope="col" class="px-4 py-3 min-w-[160px]">Kontrak &amp; Proyek</th>
                            <th scope="col" class="px-4 py-3 w-40">Jatuh Tempo</th>
                            <th scope="col" class="px-4 py-3 min-w-[150px] text-right">Nominal (Rp)</th>
                            <th scope="col" class="px-4 py-3 w-36 text-center">Status</th>
                            <th scope="col" class="px-4 py-3 w-36 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-termin" class="divide-y divide-slate-100 bg-white">
                        {{-- Baris dinamis di-render via Javascript --}}
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-3 bg-slate-50/50 border-t border-slate-100 text-xs text-slate-400 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <span>Data termin masih dalam mode demo dan belum terhubung ke Rekap Biaya.</span>
                <span class="font-mono">FTR-K05-08 &middot; UI-K05-08</span>
            </div>
        </div>

        {{-- Modal Detail Termin --}}
        <div
            id="modal-detail"
            class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4 transition-opacity"
            aria-modal="true"
            role="dialog"
        >
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xl max-w-2xl w-full overflow-hidden animate-fadeIn">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs">
                            TA
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900" id="detail-judul">Rincian Termin Tenaga Ahli</h3>
                            <div class="text-[11px] text-slate-400 font-mono" id="detail-kode">TRM-2026-001</div>
                        </div>
                    </div>
                    <button type="button" id="btn-close-modal-detail" class="text-slate-400 hover:text-slate-600 text-lg font-bold p-1">
                        &times;
                    </button>
                </div>

                <div class="p-6 space-y-5">
                    {{-- Status Badge & Nominal Header --}}
                    <div class="p-4 bg-slate-50 rounded-xl border border-slate-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <div class="text-xs text-slate-500 font-medium">Nominal Termin:</div>
                            <div class="text-2xl font-bold text-slate-900 font-mono mt-0.5" id="detail-nominal">Rp 0</div>
                        </div>
                        <div class="text-left sm:text-right space-y-1">
                            <div class="text-xs text-slate-400">Status Pembayaran:</div>
                            <span id="detail-badge-status" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                Disetujui PM
                            </span>
                        </div>
                    </div>

                    {{-- Informasi 2 Kolom --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div class="space-y-1">
                            <span class="text-slate-400">Tenaga Ahli:</span>
                            <div class="font-semibold text-slate-800 text-sm" id="detail-expert-nama">&mdash;</div>
                            <div class="text-slate-500" id="detail-expert-peran">&mdash;</div>
                            <div class="text-[11px] text-slate-400 font-mono" id="detail-expert-id">EXP-001</div>
                        </div>

                        <div class="space-y-1">
                            <span class="text-slate-400">Kontrak &amp; Proyek:</span>
                            <div class="font-semibold text-slate-800" id="detail-kontrak">&mdash;</div>
                            <div class="text-slate-600" id="detail-proyek">&mdash;</div>
                        </div>

                        <div class="space-y-1">
                            <span class="text-slate-400">Jatuh Tempo:</span>
                            <div class="font-medium text-slate-800" id="detail-jatuh-tempo">&mdash;</div>
                        </div>

                        <div class="space-y-1">
                            <span class="text-slate-400">Tanggal Pembayaran:</span>
                            <div class="font-medium text-slate-800" id="detail-tanggal-bayar">&mdash;</div>
                        </div>
                    </div>

                    {{-- Deliverable / Catatan --}}
                    <div class="space-y-1.5 pt-2 border-t border-slate-100">
                        <span class="text-xs font-semibold text-slate-700">Rincian Deliverable / Lingkup Pekerjaan:</span>
                        <div class="p-3 bg-slate-50/70 border border-slate-200 rounded-lg text-xs text-slate-700 leading-relaxed" id="detail-deliverable">
                            &mdash;
                        </div>
                    </div>

                    {{-- Catatan Verifikasi --}}
                    <div class="space-y-1.5">
                        <span class="text-xs font-semibold text-slate-700">Catatan Verifikasi &amp; Approval:</span>
                        <div class="text-xs text-slate-600 italic bg-amber-50/50 p-2.5 rounded-lg border border-amber-200/60" id="detail-catatan-verifikasi">
                            &mdash;
                        </div>
                    </div>
                </div>

                {{-- Footer Tombol Modal Detail --}}
                <div class="px-6 py-4 bg-slate-50/80 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3">
                    <button
                        type="button"
                        id="btn-detail-edit"
                        class="px-3.5 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors shadow-2xs"
                    >
                        Ubah data
                    </button>

                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            id="btn-detail-approve"
                            class="px-3.5 py-2 text-xs font-semibold text-blue-700 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition-colors shadow-2xs"
                        >
                            Verifikasi PM (Setujui)
                        </button>
                        <button
                            type="button"
                            id="btn-detail-bayar"
                            class="px-3.5 py-2 text-xs font-semibold text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition-colors shadow-2xs"
                        >
                            Catat Pembayaran (Dibayar)
                        </button>
                        <button
                            type="button"
                            id="btn-close-detail-secondary"
                            class="px-3.5 py-2 text-xs font-medium text-slate-600 hover:text-slate-900 transition-colors"
                        >
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal Form Tambah / Edit Termin --}}
        <div
            id="modal-form"
            class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4 transition-opacity"
            aria-modal="true"
            role="dialog"
        >
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xl max-w-xl w-full overflow-hidden animate-fadeIn">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <h3 class="text-sm font-bold text-slate-900" id="form-modal-title">Tambah Termin Tenaga Ahli</h3>
                    <button type="button" id="btn-close-modal-form" class="text-slate-400 hover:text-slate-600 text-lg font-bold p-1">
                        &times;
                    </button>
                </div>

                <form id="form-termin" novalidate class="p-6 space-y-4">
                    <input type="hidden" id="input-form-id" value="">

                    {{-- Pilih Tenaga Ahli --}}
                    <div class="space-y-1.5">
                        <label for="select-form-expert" class="block text-xs font-semibold text-slate-700">
                            Tenaga ahli <span class="text-rose-500">*</span>
                        </label>
                        <select
                            id="select-form-expert"
                            class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-sm text-slate-800 focus:outline-hidden focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors shadow-2xs"
                            required
                        >
                            <option value="EXP-001" data-nama="Laras Anindita" data-peran="Konsultan Arsitektur Perangkat Lunak">
                                EXP-001 &mdash; Laras Anindita (Arsitektur Software)
                            </option>
                            <option value="EXP-002" data-nama="Dr. Hendra Wijaya" data-peran="Spesialis Keamanan Siber & Audit Sistem">
                                EXP-002 &mdash; Dr. Hendra Wijaya (Keamanan Siber &amp; Audit)
                            </option>
                            <option value="EXP-003" data-nama="Maya Puspita, S.Kom." data-peran="Konsultan UI/UX Design System">
                                EXP-003 &mdash; Maya Puspita, S.Kom. (UI/UX Design System)
                            </option>
                            <option value="CUSTOM" data-nama="Tenaga Ahli Kustom" data-peran="Konsultan Spesialis">
                                + Input Tenaga Ahli Lainnya…
                            </option>
                        </select>
                    </div>

                    {{-- Input Nama Tenaga Ahli Kustom (Jika dipilih CUSTOM) --}}
                    <div id="wrapper-expert-kustom" class="hidden grid grid-cols-1 sm:grid-cols-2 gap-3 p-3 bg-slate-50 rounded-lg border border-slate-200">
                        <div class="space-y-1">
                            <label for="input-kustom-nama" class="block text-[11px] font-semibold text-slate-700">Nama Ahli</label>
                            <input type="text" id="input-kustom-nama" placeholder="Contoh: Ir. Budi Santoso" class="w-full bg-white border border-slate-300 rounded-md px-2.5 py-1.5 text-xs text-slate-800">
                        </div>
                        <div class="space-y-1">
                            <label for="input-kustom-peran" class="block text-[11px] font-semibold text-slate-700">Spesialisasi / Peran</label>
                            <input type="text" id="input-kustom-peran" placeholder="Contoh: Lead Database Architect" class="w-full bg-white border border-slate-300 rounded-md px-2.5 py-1.5 text-xs text-slate-800">
                        </div>
                    </div>

                    {{-- Judul / Nama Termin --}}
                    <div class="space-y-1.5">
                        <label for="input-form-nama" class="block text-xs font-semibold text-slate-700">
                            Nama / judul termin <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="input-form-nama"
                            placeholder="Contoh: Termin 1 - Desain Arsitektur & BAST Fase 1"
                            class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-sm text-slate-800 transition-colors focus:outline-hidden focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-2xs"
                            required
                        >
                        <p id="error-form-nama" class="hidden text-xs text-rose-600 font-medium"></p>
                    </div>

                    {{-- Kontrak & Proyek --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label for="input-form-kontrak" class="block text-xs font-semibold text-slate-700">
                                Nomor kontrak <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="input-form-kontrak"
                                placeholder="Contoh: KTR/2026/XI/004"
                                value="KTR/2026/XI/004"
                                class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-sm text-slate-800 transition-colors focus:outline-hidden focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-2xs"
                                required
                            >
                            <p id="error-form-kontrak" class="hidden text-xs text-rose-600 font-medium"></p>
                        </div>
                        <div class="space-y-1.5">
                            <label for="input-form-proyek" class="block text-xs font-semibold text-slate-700">
                                Nama proyek <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="input-form-proyek"
                                placeholder="Contoh: ERP Anaheim Nimbus"
                                value="ERP Anaheim Nimbus"
                                class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-sm text-slate-800 transition-colors focus:outline-hidden focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-2xs"
                                required
                            >
                            <p id="error-form-proyek" class="hidden text-xs text-rose-600 font-medium"></p>
                        </div>
                    </div>

                    {{-- Periode & Nominal --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label for="select-form-periode" class="block text-xs font-semibold text-slate-700">
                                Periode payroll <span class="text-rose-500">*</span>
                            </label>
                            <select
                                id="select-form-periode"
                                class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-sm text-slate-800 focus:outline-hidden focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors shadow-2xs"
                            >
                                @forelse($periods ?? [] as $p)
                                    @php
                                        $pUuid = $p->uuid ?: $p->id;
                                        $pNama = $p->nama_periode ?: \App\Models\PayrollPeriod::generateNamaPeriode($p->tanggal_mulai);
                                    @endphp
                                    <option value="{{ $pUuid }}">{{ $pNama }}</option>
                                @empty
                                    <option value="01a1193d-2500-7c04-854b-8bf03c711dde">November 2026</option>
                                @endforelse
                            </select>
                        </div>

                        <div class="space-y-1.5">
                            <label for="input-form-nominal" class="block text-xs font-semibold text-slate-700">
                                Nominal termin (Rp) <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-xs font-semibold text-slate-400">
                                    Rp
                                </span>
                                <input
                                    type="number"
                                    id="input-form-nominal"
                                    min="1000"
                                    step="1000"
                                    placeholder="25000000"
                                    class="w-full bg-white border border-slate-300 rounded-lg pl-9 pr-3 py-2 text-sm text-slate-800 font-mono transition-colors focus:outline-hidden focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-2xs"
                                    required
                                >
                            </div>
                            <p id="error-form-nominal" class="hidden text-xs text-rose-600 font-medium"></p>
                        </div>
                    </div>

                    {{-- Tanggal Jatuh Tempo & Tanggal Pembayaran --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label for="input-form-jatuh-tempo" class="block text-xs font-semibold text-slate-700">
                                Tanggal jatuh tempo <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="date"
                                id="input-form-jatuh-tempo"
                                class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-sm text-slate-800 transition-colors focus:outline-hidden focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-2xs"
                                required
                            >
                            <p id="error-form-jatuh-tempo" class="hidden text-xs text-rose-600 font-medium"></p>
                        </div>

                        <div class="space-y-1.5">
                            <label for="input-form-tanggal-bayar" class="block text-xs font-semibold text-slate-700">
                                Tanggal pembayaran <span class="text-slate-400 font-normal">(opsional)</span>
                            </label>
                            <input
                                type="date"
                                id="input-form-tanggal-bayar"
                                class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-sm text-slate-800 transition-colors focus:outline-hidden focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-2xs"
                            >
                            <p class="text-[11px] text-slate-400">Diisi jika termin sudah terealisasi.</p>
                        </div>
                    </div>

                    {{-- Status Termin --}}
                    <div class="space-y-1.5">
                        <label for="select-form-status" class="block text-xs font-semibold text-slate-700">
                            Status termin <span class="text-rose-500">*</span>
                        </label>
                        <select
                            id="select-form-status"
                            class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-sm text-slate-800 focus:outline-hidden focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors shadow-2xs cursor-pointer"
                        >
                            <option value="Draf">Draf</option>
                            <option value="Menunggu Verifikasi PM">Menunggu Verifikasi PM</option>
                            <option value="Disetujui PM">Disetujui PM</option>
                            <option value="Dibayar">Dibayar</option>
                        </select>
                    </div>

                    {{-- Deliverable / Catatan --}}
                    <div class="space-y-1.5">
                        <label for="textarea-form-deliverable" class="block text-xs font-semibold text-slate-700">
                            Deliverable / lingkup pekerjaan
                        </label>
                        <textarea
                            id="textarea-form-deliverable"
                            rows="2"
                            placeholder="Tuliskan milestone yang harus diselesaikan untuk pencairan termin ini…"
                            class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-sm text-slate-800 transition-colors focus:outline-hidden focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-2xs"
                        ></textarea>
                    </div>

                    {{-- Action Footer Form --}}
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button
                            type="button"
                            id="btn-batal-form"
                            class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors shadow-2xs"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            id="btn-simpan-form"
                            class="inline-flex items-center gap-2 px-5 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 active:bg-blue-800 focus:outline-hidden focus:ring-2 focus:ring-blue-500 disabled:opacity-60 transition-colors shadow-xs"
                        >
                            <svg id="icon-form-spinner" class="hidden w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span id="text-tombol-form-simpan">Simpan termin</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Footer Layar (Sesuai Konvensi ERP F-5) --}}
        <div class="flex items-center justify-between text-xs text-slate-400 pt-2">
            <span>Seluruh nama, kontrak, dan angka termin adalah data contoh simulasi demo.</span>
            <span>UI-K05-08</span>
        </div>

    </div>
</x-layouts.app>

