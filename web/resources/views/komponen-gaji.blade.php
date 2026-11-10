<x-layouts.app>
    <x-slot:title>Komponen Gaji - Universal ERP</x-slot:title>
    <x-slot:sessionUser>Petugas payroll &middot; autentikasi simulasi</x-slot:sessionUser>
    <x-slot:breadcrumb>Komponen gaji</x-slot:breadcrumb>
    <x-slot:pageTitle>Komponen gaji</x-slot:pageTitle>
    <x-slot:pageSubtitle>Atur gaji pokok, bonus, BPJS, serta rincian komponen pendapatan dan potongan karyawan.</x-slot:pageSubtitle>

    <x-slot:headerActions>
        <div class="flex items-center gap-2">
            <span id="badge-api-mode" class="inline-flex items-center gap-1.5 text-xs font-medium text-blue-700 bg-blue-50 border border-blue-200 px-2.5 py-1 rounded-full">
                <span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span>
                API Laravel · autentikasi simulasi
            </span>
            <a
                id="btn-nav-ke-periode"
                href="{{ url('/periode-payroll') }}"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 hover:text-blue-600 transition-colors shadow-2xs"
                title="Buka halaman Periode Payroll"
            >
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span>Periode payroll</span>
            </a>
            <a
                id="btn-nav-ke-pratinjau"
                href="{{ url('/pratinjau-perhitungan') }}"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors shadow-2xs"
                title="Lihat Pratinjau Perhitungan"
            >
                <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                <span>Pratinjau perhitungan</span>
                <svg class="w-3.5 h-3.5 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
    </x-slot:headerActions>

    <div
        id="komponen-gaji-app"
        class="space-y-6"
        data-initial-periods='@json($periods ?? [])'
        data-selected-periode-id="{{ $selectedPeriodeId ?? '' }}"
        data-selected-employee-id="{{ $selectedEmployeeId ?? '' }}"
        data-api-token="mock-payroll"
    >
        <div class="flex flex-col sm:flex-row sm:items-center gap-2 p-4 bg-white border border-slate-200 rounded-xl">
            <label for="select-data-mode" class="text-xs font-semibold text-slate-700">Sumber data</label>
            <select id="select-data-mode" class="w-full sm:w-auto bg-white border border-slate-300 rounded-lg px-3 py-2 text-sm text-slate-800 focus:outline-hidden focus:ring-2 focus:ring-blue-500">
                <option value="api" selected>API Laravel (autentikasi mock)</option>
                <option value="demo">Mode demo lokal</option>
            </select>
            <p class="text-xs text-slate-500">Token mock-payroll hanya simulasi, bukan autentikasi produksi. Data demo localStorage tidak dikirim ke API.</p>
        </div>

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
                <div class="font-semibold text-emerald-900">Perubahan berhasil disimpan</div>
                <div id="text-sukses" class="text-xs text-emerald-700 mt-0.5">Komponen gaji berhasil divalidasi dan diperbarui.</div>
            </div>
            <button type="button" id="btn-close-alert-sukses" class="text-emerald-500 hover:text-emerald-700 text-xs font-bold px-2 py-1">
                &times;
            </button>
        </div>

        {{-- Banner Alert Error --}}
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
                <div class="font-semibold text-rose-900" id="title-error">Terjadi kesalahan validasi formulir</div>
                <div id="text-error" class="text-xs text-rose-700 mt-0.5">Silakan periksa kembali nilai dan nama komponen gaji yang dimasukkan.</div>
                <ul id="list-error" class="mt-2 text-xs text-rose-700 list-disc list-inside space-y-1 hidden"></ul>
            </div>
            <button type="button" id="btn-close-alert-error" class="text-rose-500 hover:text-rose-700 text-xs font-bold px-2 py-1">
                &times;
            </button>
        </div>

        {{-- Kartu Pemilihan Periode dan Karyawan --}}
        <x-ui.card>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- Selector Periode --}}
                <div class="space-y-1.5">
                    <label for="select-periode" class="block text-xs font-semibold text-slate-700">
                        Periode payroll
                    </label>
                    <select
                        id="select-periode"
                        class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-sm text-slate-800 focus:outline-hidden focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors shadow-2xs"
                    >
                        @forelse($periods ?? [] as $p)
                            @php
                                $pUuid = $p->uuid ?: $p->id;
                                $pNama = $p->nama_periode ?: \App\Models\PayrollPeriod::generateNamaPeriode($p->tanggal_mulai);
                                $isCurrentSelected = ($selectedPeriodeId && strtolower($selectedPeriodeId) === strtolower($pUuid)) || (!$selectedPeriodeId && $loop->first);
                            @endphp
                            <option value="{{ $pUuid }}" {{ $isCurrentSelected ? 'selected' : '' }}>
                                {{ $pNama }} ({{ $p->status ?? 'Draf' }})
                            </option>
                        @empty
                            <option value="" disabled selected>Belum ada periode tersimpan</option>
                        @endforelse
                    </select>
                    <p id="info-periode-rentang" class="text-[11px] text-slate-500 flex items-center gap-1.5 pt-1">
                        <span class="text-slate-400">Rentang:</span>
                        <span id="text-periode-rentang" class="font-medium text-slate-700">&mdash;</span>
                        <span class="text-slate-300">&bull;</span>
                        <span id="badge-periode-status" class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                            Draf
                        </span>
                    </p>
                </div>

                {{-- Selector Karyawan --}}
                <div class="space-y-1.5">
                    <label for="select-karyawan" class="block text-xs font-semibold text-slate-700">
                        Pilih karyawan
                    </label>
                    <select
                        id="select-karyawan"
                        class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-sm text-slate-800 focus:outline-hidden focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors shadow-2xs"
                    >
                        <option value="EMP-001" selected>EMP-001 &mdash; Referensi demo cuti F-4</option>
                        <option value="EMP-002">EMP-002 &mdash; Karyawan 2 (Data Demo Tambahan)</option>
                        <option value="EMP-003">EMP-003 &mdash; Karyawan 3 (Data Demo Tambahan)</option>
                        <option value="EMP-999">EMP-999 &mdash; Karyawan Baru (Belum Ada Konfigurasi)</option>
                    </select>
                    <p id="info-karyawan" class="text-[11px] text-slate-500 flex flex-wrap items-center gap-1.5 pt-1">
                        <span class="text-slate-400">Sumber ID:</span>
                        <span id="text-karyawan-sumber" class="font-medium text-slate-700">Referensi demo cuti F-4</span>
                        <span class="text-slate-300">&bull;</span>
                        <span class="text-slate-400">Status Profil:</span>
                        <span id="text-karyawan-profil" class="text-slate-500">Belum tersedia</span>
                    </p>
                </div>

            </div>

            {{-- Panel Pengujian Demo (Accordion) --}}
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
                    <span>Pengujian skenario demo (Kondisi UI)</span>
                </button>

                <div id="panel-pengujian" class="hidden mt-3">
                    <div class="p-4 bg-slate-50 border border-slate-200 rounded-lg space-y-3">
                        <p class="text-[11px] text-slate-500">
                            Fitur ini mempermudah pengujian kondisi UI: memuat, kosong, berhasil, dan gagal pada Mode demo.
                        </p>
                        <div class="flex flex-wrap items-center gap-2">
                            <button
                                type="button"
                                id="btn-uji-normal"
                                class="px-2.5 py-1 text-xs font-medium rounded-md bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 transition-colors shadow-2xs"
                            >
                                Skenario Normal (EMP-001)
                            </button>
                            <button
                                type="button"
                                id="btn-uji-kosong"
                                class="px-2.5 py-1 text-xs font-medium rounded-md bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 transition-colors shadow-2xs"
                            >
                                Skenario Kosong (EMP-999)
                            </button>
                            <button
                                type="button"
                                id="btn-uji-gagal-validasi"
                                class="px-2.5 py-1 text-xs font-medium rounded-md bg-rose-50 border border-rose-200 text-rose-700 hover:bg-rose-100 transition-colors shadow-2xs"
                            >
                                Simulasi Validasi Gagal
                            </button>
                            <button
                                type="button"
                                id="btn-uji-simulasi-error"
                                class="px-2.5 py-1 text-xs font-medium rounded-md bg-amber-50 border border-amber-200 text-amber-700 hover:bg-amber-100 transition-colors shadow-2xs"
                            >
                                Simulasi Gangguan Layanan (503)
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </x-ui.card>

        {{-- State: Memuat (Loading) --}}
        <div id="state-loading" class="hidden py-12 text-center">
            <div class="inline-flex items-center gap-3 px-5 py-3 rounded-xl bg-white border border-slate-200 shadow-xs">
                <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-sm font-medium text-slate-700">Memuat rincian komponen gaji karyawan…</span>
            </div>
        </div>

        {{-- State Kosong / Belum Ada Komponen (Banner Khusus Data Kosong) --}}
        <div id="state-empty-banner" class="hidden p-6 bg-slate-50 border border-dashed border-slate-300 rounded-xl text-center space-y-3">
            <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-slate-800">Komponen Gaji Belum Dikonfigurasi</h3>
                <p class="text-xs text-slate-500 mt-1 max-w-md mx-auto">
                    Karyawan ini belum memiliki penetapan gaji pokok maupun komponen tambahan pada periode terpilih. Silakan isi formulir di bawah ini untuk menetapkannya.
                </p>
            </div>
            <div>
                <button
                    type="button"
                    id="btn-isi-template-standar"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-blue-700 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition-colors shadow-2xs"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Muat contoh komponen awal</span>
                </button>
            </div>
        </div>

        {{-- Main Form Container --}}
        <form id="form-komponen-gaji" novalidate class="space-y-6">

            {{-- 1. Kartu Form Gaji Pokok, Bonus, dan BPJS --}}
            <div class="bg-white rounded-xl border border-slate-200/90 shadow-xs overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-semibold text-slate-900 tracking-tight">
                            Gaji pokok &amp; agregat dasar
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Tetapkan gaji pokok bulanan, bonus periode, serta iuran BPJS ketenagakerjaan dan kesehatan.
                        </p>
                    </div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-600">
                        Aturan ERP F-5
                    </span>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

                        {{-- Gaji Pokok --}}
                        <div class="space-y-1.5">
                            <label for="input-gaji-pokok" class="block text-xs font-semibold text-slate-700">
                                Gaji pokok <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-xs font-semibold text-slate-400">
                                    Rp
                                </span>
                                <input
                                    type="number"
                                    id="input-gaji-pokok"
                                    name="gajiPokok"
                                    min="0"
                                    step="1000"
                                    placeholder="0"
                                    class="w-full bg-white border border-slate-300 rounded-lg pl-9 pr-3 py-2 text-sm text-slate-800 font-mono transition-colors focus:outline-hidden focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-2xs"
                                    required
                                    aria-describedby="error-gaji-pokok"
                                >
                            </div>
                            <p class="text-[11px] text-slate-400">Komponen pendapatan utama bulanan.</p>
                            <p id="error-gaji-pokok" class="hidden text-xs text-rose-600 font-medium"></p>
                        </div>

                        {{-- Bonus --}}
                        <div class="space-y-1.5">
                            <label for="input-bonus" class="block text-xs font-semibold text-slate-700">
                                Bonus periode
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-xs font-semibold text-slate-400">
                                    Rp
                                </span>
                                <input
                                    type="number"
                                    id="input-bonus"
                                    name="bonus"
                                    min="0"
                                    step="1000"
                                    placeholder="0"
                                    class="w-full bg-white border border-slate-300 rounded-lg pl-9 pr-3 py-2 text-sm text-slate-800 font-mono transition-colors focus:outline-hidden focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-2xs"
                                    aria-describedby="error-bonus"
                                >
                            </div>
                            <p class="text-[11px] text-slate-400">Sudah termasuk dalam total pendapatan.</p>
                            <p id="error-bonus" class="hidden text-xs text-rose-600 font-medium"></p>
                        </div>

                        {{-- BPJS Karyawan --}}
                        <div class="space-y-1.5">
                            <label for="input-bpjs-karyawan" class="block text-xs font-semibold text-slate-700">
                                BPJS karyawan
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-xs font-semibold text-slate-400">
                                    Rp
                                </span>
                                <input
                                    type="number"
                                    id="input-bpjs-karyawan"
                                    name="bpjsKaryawan"
                                    min="0"
                                    step="1000"
                                    placeholder="0"
                                    class="w-full bg-white border border-slate-300 rounded-lg pl-9 pr-3 py-2 text-sm text-slate-800 font-mono transition-colors focus:outline-hidden focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-2xs"
                                    aria-describedby="error-bpjs-karyawan"
                                >
                            </div>
                            <p class="text-[11px] text-slate-400">Potongan BPJS karyawan (terpisah).</p>
                            <p id="error-bpjs-karyawan" class="hidden text-xs text-rose-600 font-medium"></p>
                        </div>

                        {{-- BPJS Perusahaan --}}
                        <div class="space-y-1.5">
                            <label for="input-bpjs-perusahaan" class="block text-xs font-semibold text-slate-700">
                                BPJS perusahaan
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-xs font-semibold text-slate-400">
                                    Rp
                                </span>
                                <input
                                    type="number"
                                    id="input-bpjs-perusahaan"
                                    name="bpjsPerusahaan"
                                    min="0"
                                    step="1000"
                                    placeholder="0"
                                    class="w-full bg-white border border-slate-300 rounded-lg pl-9 pr-3 py-2 text-sm text-slate-800 font-mono transition-colors focus:outline-hidden focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-2xs"
                                    aria-describedby="error-bpjs-perusahaan"
                                >
                            </div>
                            <p class="text-[11px] text-slate-400">Ditanggung perusahaan (tidak potong gaji).</p>
                            <p id="error-bpjs-perusahaan" class="hidden text-xs text-rose-600 font-medium"></p>
                        </div>

                    </div>
                </div>
            </div>

            {{-- 2. Ringkasan Kalkulasi Real-time (Stat Cards) --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                {{-- Total Pendapatan --}}
                <div class="bg-white p-4 rounded-xl border border-slate-200/90 shadow-2xs">
                    <div class="text-xs font-medium text-slate-500">Total Pendapatan</div>
                    <div id="stat-total-pendapatan" class="text-lg font-bold text-slate-900 font-mono mt-1">Rp 0</div>
                    <div class="text-[10px] text-slate-400 mt-0.5">Gaji pokok + bonus + komponen</div>
                </div>

                {{-- Total Potongan Komponen --}}
                <div class="bg-white p-4 rounded-xl border border-slate-200/90 shadow-2xs">
                    <div class="text-xs font-medium text-slate-500">Potongan Komponen</div>
                    <div id="stat-total-potongan" class="text-lg font-bold text-rose-600 font-mono mt-1">Rp 0</div>
                    <div class="text-[10px] text-slate-400 mt-0.5">Di luar BPJS karyawan</div>
                </div>

                {{-- BPJS Karyawan --}}
                <div class="bg-white p-4 rounded-xl border border-slate-200/90 shadow-2xs">
                    <div class="text-xs font-medium text-slate-500">BPJS Karyawan</div>
                    <div id="stat-bpjs-karyawan" class="text-lg font-bold text-slate-700 font-mono mt-1">Rp 0</div>
                    <div class="text-[10px] text-slate-400 mt-0.5">Potongan premi karyawan</div>
                </div>

                {{-- Estimasi Gaji Bersih --}}
                <div class="bg-blue-50/70 p-4 rounded-xl border border-blue-200 shadow-2xs">
                    <div class="text-xs font-medium text-blue-900">Estimasi Gaji Bersih</div>
                    <div id="stat-gaji-bersih" class="text-lg font-bold text-blue-700 font-mono mt-1">Rp 0</div>
                    <div class="text-[10px] text-blue-600/80 mt-0.5">Pendapatan &minus; potongan &minus; BPJS</div>
                </div>
            </div>

            {{-- 3. Kartu Baris Komponen Pendapatan / Potongan --}}
            <div class="bg-white rounded-xl border border-slate-200/90 shadow-xs overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h2 class="text-base font-semibold text-slate-900 tracking-tight">
                            Rincian komponen gaji tambahan
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Daftar tunjangan (pendapatan) atau potongan spesifik untuk karyawan ini.
                        </p>
                    </div>
                    <button
                        type="button"
                        id="btn-tambah-baris"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 active:bg-blue-800 transition-colors shadow-2xs shrink-0"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>Tambah baris</span>
                    </button>
                </div>

                <div class="p-6">
                    {{-- Container Tabel Komponen --}}
                    <div class="overflow-x-auto border border-slate-200 rounded-lg">
                        <table class="w-full text-left text-sm text-slate-700" id="table-komponen">
                            <thead class="bg-slate-50/75 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200/80">
                                <tr>
                                    <th scope="col" class="px-4 py-3 min-w-[220px]">Nama komponen <span class="text-rose-500">*</span></th>
                                    <th scope="col" class="px-4 py-3 w-44">Jenis</th>
                                    <th scope="col" class="px-4 py-3 min-w-[180px]">Nominal (Rp) <span class="text-rose-500">*</span></th>
                                    <th scope="col" class="px-4 py-3 w-36 text-center">Status</th>
                                    <th scope="col" class="px-4 py-3 w-20 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-komponen" class="divide-y divide-slate-100 bg-white">
                                {{-- Baris dinamis dirender via JS --}}
                            </tbody>
                        </table>
                    </div>

                    {{-- Placeholder Saat Baris Kosong --}}
                    <div id="state-tabel-kosong" class="hidden p-8 text-center bg-slate-50/50 rounded-lg border border-dashed border-slate-200 mt-4">
                        <svg class="w-8 h-8 text-slate-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-sm font-semibold text-slate-700">Belum ada komponen tambahan</p>
                        <p class="text-xs text-slate-500 mt-1">Klik tombol &ldquo;Tambah baris&rdquo; di atas untuk menyisipkan tunjangan atau potongan baru.</p>
                    </div>
                </div>

                {{-- Action Footer Formulir --}}
                <div class="px-6 py-4 bg-slate-50/60 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <p class="text-xs text-slate-500">
                        Pastikan semua nama komponen dan nominal terisi dengan benar sebelum menyimpan.
                    </p>

                    <div class="flex items-center gap-3">
                        <button
                            type="button"
                            id="btn-reset-form"
                            class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 focus:outline-hidden focus:ring-2 focus:ring-blue-500 transition-colors shadow-2xs"
                        >
                            Reset
                        </button>

                        <button
                            type="submit"
                            id="btn-simpan-komponen"
                            class="inline-flex items-center gap-2 px-5 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 active:bg-blue-800 focus:outline-hidden focus:ring-2 focus:ring-blue-500 disabled:opacity-60 disabled:cursor-wait transition-colors shadow-xs"
                        >
                            <svg id="icon-simpan-spinner" class="hidden w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span id="text-tombol-simpan">Simpan komponen</span>
                        </button>
                    </div>
                </div>

            </div>

        </form>

        {{-- Footer Layar (Sesuai Konvensi UI ERP F-5) --}}
        <div class="flex items-center justify-between text-xs text-slate-400 pt-2">
            <span>Seluruh nama, tanggal, dan angka adalah data contoh.</span>
            <span>UI-K05-02</span>
        </div>

    </div>
</x-layouts.app>
