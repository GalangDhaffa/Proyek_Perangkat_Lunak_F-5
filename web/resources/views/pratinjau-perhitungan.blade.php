<x-layouts.app>
    <x-slot:title>Pratinjau Perhitungan - Universal ERP</x-slot:title>
    <x-slot:sessionUser>Petugas payroll &middot; Sesi demo</x-slot:sessionUser>
    <x-slot:breadcrumb>Pratinjau perhitungan</x-slot:breadcrumb>
    <x-slot:pageTitle>Pratinjau perhitungan</x-slot:pageTitle>
    <x-slot:pageSubtitle>Kalkulasi rincian pendapatan kotor, bonus, potongan komponen, iuran BPJS, dan estimasi gaji bersih karyawan per periode.</x-slot:pageSubtitle>

    <x-slot:headerActions>
        <div class="flex flex-wrap items-center gap-2">
            <span id="badge-api-mode" class="inline-flex items-center gap-1.5 text-xs font-medium text-amber-700 bg-amber-50 border border-amber-200 px-2.5 py-1 rounded-full">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                Mode demo
            </span>
            <a
                id="btn-nav-ke-komponen"
                href="{{ url('/komponen-gaji') }}"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 hover:text-blue-600 transition-colors shadow-2xs"
                title="Buka halaman Komponen Gaji"
            >
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                <span>Komponen gaji</span>
            </a>
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
        </div>
    </x-slot:headerActions>

    <div
        id="pratinjau-perhitungan-app"
        class="space-y-6"
        data-initial-periods='@json($periods ?? [])'
        data-selected-periode-id="{{ $selectedPeriodeId ?? '' }}"
        data-selected-employee-id="{{ $selectedEmployeeId ?? '' }}"
    >

        {{-- Alert Sukses --}}
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
                <div class="font-semibold text-emerald-900">Perhitungan Berhasil Diproses</div>
                <div id="text-sukses" class="text-xs text-emerald-700 mt-0.5">Rincian pratinjau payroll telah dimutakhirkan.</div>
            </div>
            <button type="button" id="btn-close-alert-sukses" class="text-emerald-500 hover:text-emerald-700 text-xs font-bold px-2 py-1">
                &times;
            </button>
        </div>

        {{-- Alert Stale / Hasil Lama Perlu Dihitung Ulang --}}
        <div
            id="alert-stale"
            class="hidden p-4 text-sm font-medium text-amber-800 bg-amber-50 border border-amber-200 rounded-xl items-start gap-3 transition-all"
            role="alert"
        >
            <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center shrink-0 mt-0.5">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div class="flex-1">
                <div class="font-semibold text-amber-900">Pilihan Periode atau Karyawan Telah Berubah</div>
                <div class="text-xs text-amber-700 mt-0.5">
                    Hasil kalkulasi yang tampil di bawah ini adalah data sebelumnya (stale). Klik tombol <strong class="font-semibold text-amber-900">&ldquo;Hitung Pratinjau&rdquo;</strong> di bawah untuk memproses perhitungan ulang yang mutakhir.
                </div>
            </div>
            <button
                type="button"
                id="btn-rehitung-cepat"
                class="px-3 py-1 text-xs font-semibold text-amber-800 bg-white border border-amber-300 rounded-lg hover:bg-amber-100 transition-colors shadow-2xs shrink-0"
            >
                Hitung ulang sekarang
            </button>
        </div>

        {{-- Alert Error / Gagal --}}
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
                <div class="flex flex-wrap items-center gap-2">
                    <span id="error-code-badge" class="font-mono text-[11px] font-bold text-rose-800 px-2 py-0.5 rounded bg-rose-100 border border-rose-200">
                        CALCULATION_ERROR
                    </span>
                    <span class="font-semibold text-rose-900" id="title-error">Gagal Memproses Pratinjau Perhitungan</span>
                </div>
                <div id="text-error" class="text-xs text-rose-700 mt-1">
                    Terjadi kesalahan saat memanggil API backend payroll atau layanan tidak dapat dihubungi.
                </div>
                <div class="mt-3 flex items-center gap-2">
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
                        class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-sm text-slate-800 focus:outline-hidden focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors shadow-2xs cursor-pointer"
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
                        class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-sm text-slate-800 focus:outline-hidden focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors shadow-2xs cursor-pointer"
                    >
                        <option value="EMP-001" {{ ($selectedEmployeeId ?? 'EMP-001') === 'EMP-001' ? 'selected' : '' }}>
                            EMP-001 &mdash; Referensi demo cuti F-4
                        </option>
                        <option value="EMP-002" {{ ($selectedEmployeeId ?? '') === 'EMP-002' ? 'selected' : '' }}>
                            EMP-002 &mdash; Karyawan 2 (Data Demo Tambahan)
                        </option>
                        <option value="EMP-003" {{ ($selectedEmployeeId ?? '') === 'EMP-003' ? 'selected' : '' }}>
                            EMP-003 &mdash; Karyawan 3 (Data Demo Tambahan)
                        </option>
                        <option value="EMP-999" {{ ($selectedEmployeeId ?? '') === 'EMP-999' ? 'selected' : '' }}>
                            EMP-999 &mdash; Karyawan Baru (Belum Ada Konfigurasi)
                        </option>
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

            {{-- Action Row: Tombol Hitung Pratinjau --}}
            <div class="mt-6 pt-4 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        id="btn-hitung-pratinjau"
                        class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 active:bg-blue-800 focus:outline-hidden focus:ring-2 focus:ring-blue-500 disabled:opacity-60 disabled:cursor-wait transition-colors shadow-xs"
                    >
                        <svg id="icon-hitung-spinner" class="hidden w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <svg id="icon-hitung-calc" class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        <span id="text-tombol-hitung">Hitung Pratinjau</span>
                    </button>

                    <a
                        id="link-ubah-komponen"
                        href="{{ url('/komponen-gaji') }}"
                        class="text-xs font-semibold text-slate-600 hover:text-blue-600 transition-colors flex items-center gap-1"
                        title="Buka form penetapan komponen gaji"
                    >
                        <span>Ubah komponen gaji</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                    </a>
                </div>

                {{-- Status Badge Kalkulasi --}}
                <div class="flex items-center gap-2">
                    <span id="badge-status-kalkulasi" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200">
                        Belum dihitung
                    </span>
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
                    <span>Pengujian skenario demo (Kondisi UI Pratinjau)</span>
                </button>

                <div id="panel-pengujian" class="hidden mt-3">
                    <div class="p-4 bg-slate-50 border border-slate-200 rounded-lg space-y-3">
                        <p class="text-[11px] text-slate-500">
                            Pilih skenario di bawah ini untuk menguji seluruh kondisi antarmuka pratinjau: memuat, data lengkap, belum ada konfigurasi, gagal dengan coba lagi, serta tanda hasil lama (stale).
                        </p>
                        <div class="flex flex-wrap items-center gap-2">
                            <button
                                type="button"
                                id="btn-uji-hitung-normal"
                                class="px-2.5 py-1 text-xs font-medium rounded-md bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 transition-colors shadow-2xs"
                            >
                                Hitung Normal (EMP-001)
                            </button>
                            <button
                                type="button"
                                id="btn-uji-tanpa-konfigurasi"
                                class="px-2.5 py-1 text-xs font-medium rounded-md bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 transition-colors shadow-2xs"
                            >
                                Belum Ada Konfigurasi (EMP-999)
                            </button>
                            <button
                                type="button"
                                id="btn-uji-simulasi-gagal"
                                class="px-2.5 py-1 text-xs font-medium rounded-md bg-rose-50 border border-rose-200 text-rose-700 hover:bg-rose-100 transition-colors shadow-2xs"
                            >
                                Simulasi Gagal (503 &amp; Coba Lagi)
                            </button>
                            <button
                                type="button"
                                id="btn-uji-tandai-stale"
                                class="px-2.5 py-1 text-xs font-medium rounded-md bg-amber-50 border border-amber-200 text-amber-700 hover:bg-amber-100 transition-colors shadow-2xs"
                            >
                                Tandai Hasil Lama (Stale)
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </x-ui.card>

        {{-- State: Memuat (Loading) --}}
        <div id="state-loading" class="hidden py-14 text-center">
            <div class="inline-flex items-center gap-3 px-6 py-3.5 rounded-xl bg-white border border-slate-200 shadow-xs">
                <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <div class="text-left">
                    <div class="text-sm font-semibold text-slate-800">Menghubungi API backend payroll…</div>
                    <div class="text-[11px] text-slate-400">Memproses kalkulasi komponen dan potongan gaji</div>
                </div>
            </div>
        </div>

        {{-- State: Belum Ada Konfigurasi Gaji --}}
        <div id="state-unconfigured" class="hidden p-8 bg-slate-50 border border-dashed border-slate-300 rounded-xl text-center space-y-3">
            <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-base font-semibold text-slate-800">Belum Ada Konfigurasi Gaji</h3>
                <p class="text-xs text-slate-500 mt-1 max-w-md mx-auto">
                    Karyawan yang dipilih belum memiliki nilai gaji pokok maupun rincian komponen pendapatan/potongan untuk periode ini. Pratinjau perhitungan baru dapat diproses setelah gaji ditetapkan.
                </p>
            </div>
            <div class="pt-2">
                <a
                    id="btn-ke-atur-komponen"
                    href="{{ url('/komponen-gaji') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors shadow-2xs"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    <span>Atur komponen gaji sekarang</span>
                </a>
            </div>
        </div>

        {{-- State: Konten Hasil Pratinjau Perhitungan --}}
        <div id="state-content" class="hidden space-y-6 transition-all">

            {{-- 1. Kartu Meta Informasi Hasil Perhitungan --}}
            <div class="bg-white border border-slate-200/90 rounded-xl p-6 shadow-xs">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <div class="flex flex-wrap items-center gap-2.5">
                            <h2 id="view-karyawan-nama" class="text-xl font-bold text-slate-900 tracking-tight">
                                &mdash;
                            </h2>
                            <span id="view-badge-status-hasil" class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                Hasil Mutakhir
                            </span>
                        </div>
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500 mt-2">
                            <div class="flex items-center gap-1">
                                <span class="text-slate-400">ID Karyawan:</span>
                                <span id="view-karyawan-id" class="font-mono font-medium text-slate-700">EMP-001</span>
                            </div>
                            <span>&bull;</span>
                            <div class="flex items-center gap-1">
                                <span class="text-slate-400">Jabatan:</span>
                                <span id="view-karyawan-jabatan" class="font-medium text-slate-700">Belum tersedia</span>
                            </div>
                            <span>&bull;</span>
                            <div class="flex items-center gap-1">
                                <span class="text-slate-400">Departemen:</span>
                                <span id="view-karyawan-dept" class="font-medium text-slate-700">Belum tersedia</span>
                            </div>
                        </div>
                    </div>

                    {{-- Metadata Kalkulasi (Waktu Hitung & Sumber) --}}
                    <div class="text-left sm:text-right border-t sm:border-t-0 pt-3 sm:pt-0 border-slate-100 text-xs text-slate-500 space-y-1">
                        <div class="flex items-center sm:justify-end gap-1.5">
                            <span class="text-slate-400">Waktu hitung:</span>
                            <span id="view-waktu-hitung" class="font-medium text-slate-800">&mdash;</span>
                        </div>
                        <div class="flex items-center sm:justify-end gap-1.5">
                            <span class="text-slate-400">Sumber data:</span>
                            <span id="view-sumber-data" class="font-medium text-blue-700 bg-blue-50 px-2 py-0.5 rounded border border-blue-200">
                                Simulasi Demo Frontend
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. Ringkasan 4 Kartu Utama --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Total Pendapatan --}}
                <div class="bg-white p-5 rounded-xl border border-slate-200/90 shadow-2xs">
                    <div class="text-xs font-medium text-slate-500">Total Pendapatan Kotor</div>
                    <div id="stat-total-pendapatan" class="text-xl font-bold text-slate-900 font-mono mt-1.5">Rp 0</div>
                    <div class="text-[11px] text-slate-400 mt-1">Pokok + bonus + tunjangan aktif</div>
                </div>

                {{-- Total Potongan Komponen --}}
                <div class="bg-white p-5 rounded-xl border border-slate-200/90 shadow-2xs">
                    <div class="text-xs font-medium text-slate-500">Potongan Komponen</div>
                    <div id="stat-total-potongan" class="text-xl font-bold text-rose-600 font-mono mt-1.5">Rp 0</div>
                    <div class="text-[11px] text-slate-400 mt-1">Di luar iuran BPJS karyawan</div>
                </div>

                {{-- BPJS Karyawan --}}
                <div class="bg-white p-5 rounded-xl border border-slate-200/90 shadow-2xs">
                    <div class="text-xs font-medium text-slate-500">Potongan BPJS Karyawan</div>
                    <div id="stat-bpjs-karyawan" class="text-xl font-bold text-slate-700 font-mono mt-1.5">Rp 0</div>
                    <div class="text-[11px] text-slate-400 mt-1">Iuran premi pekerja (terpisah)</div>
                </div>

                {{-- Estimasi Gaji Bersih --}}
                <div class="bg-blue-50/80 p-5 rounded-xl border border-blue-200 shadow-2xs">
                    <div class="text-xs font-semibold text-blue-900">Estimasi Gaji Bersih</div>
                    <div id="stat-gaji-bersih" class="text-xl font-bold text-blue-700 font-mono mt-1.5">Rp 0</div>
                    <div class="text-[11px] text-blue-600/90 mt-1 font-medium">Pendapatan &minus; Potongan &minus; BPJS</div>
                </div>
            </div>

            {{-- 3. Rincian Terstruktur Pendapatan vs Potongan (2 Kolom) --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                {{-- Kolom Kiri: Rincian Pendapatan --}}
                <div class="bg-white rounded-xl border border-slate-200/90 shadow-xs overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/40">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <h3 class="text-sm font-semibold text-slate-900 tracking-tight">Rincian Pendapatan (Penambahan)</h3>
                        </div>
                        <span class="text-xs font-semibold text-emerald-700" id="subtotal-pendapatan-header">Rp 0</span>
                    </div>

                    <div class="p-6 space-y-4">
                        <table class="w-full text-left text-sm text-slate-700">
                            <tbody class="divide-y divide-slate-100">
                                {{-- Gaji Pokok --}}
                                <tr>
                                    <td class="py-2.5 font-medium text-slate-800">Gaji Pokok</td>
                                    <td class="py-2.5 text-right font-mono text-slate-900" id="row-gaji-pokok">Rp 0</td>
                                </tr>
                                {{-- Bonus --}}
                                <tr>
                                    <td class="py-2.5">
                                        <div class="font-medium text-slate-800">Bonus Periode</div>
                                        <div class="text-[11px] text-slate-400">*Termasuk dalam total pendapatan</div>
                                    </td>
                                    <td class="py-2.5 text-right font-mono text-slate-900" id="row-bonus">Rp 0</td>
                                </tr>
                            </tbody>
                            <tbody id="tbody-komponen-pendapatan" class="divide-y divide-slate-100">
                                {{-- Komponen tambahan pendapatan via JS --}}
                            </tbody>
                        </table>

                        {{-- Subtotal Pendapatan Footer --}}
                        <div class="pt-3 border-t border-slate-200 flex items-center justify-between text-sm font-semibold text-slate-900">
                            <span>Total Pendapatan</span>
                            <span class="font-mono text-emerald-700" id="subtotal-pendapatan">Rp 0</span>
                        </div>
                    </div>
                </div>

                {{-- Kolom Kanan: Rincian Potongan & BPJS --}}
                <div class="bg-white rounded-xl border border-slate-200/90 shadow-xs overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/40">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                            <h3 class="text-sm font-semibold text-slate-900 tracking-tight">Rincian Potongan &amp; BPJS</h3>
                        </div>
                        <span class="text-xs font-semibold text-rose-700" id="subtotal-potongan-header">Rp 0</span>
                    </div>

                    <div class="p-6 space-y-4">
                        <table class="w-full text-left text-sm text-slate-700">
                            <tbody id="tbody-komponen-potongan" class="divide-y divide-slate-100">
                                {{-- Komponen tambahan potongan via JS --}}
                            </tbody>
                            <tbody class="divide-y divide-slate-100 border-t border-slate-100">
                                {{-- Potongan BPJS Karyawan --}}
                                <tr>
                                    <td class="py-2.5">
                                        <div class="font-medium text-slate-800">BPJS Karyawan</div>
                                        <div class="text-[11px] text-slate-400">*Dicatat terpisah dari potongan komponen</div>
                                    </td>
                                    <td class="py-2.5 text-right font-mono text-rose-600" id="row-bpjs-karyawan">Rp 0</td>
                                </tr>
                                {{-- BPJS Perusahaan (Info) --}}
                                <tr class="bg-slate-50/50">
                                    <td class="py-2.5 px-2">
                                        <div class="font-medium text-slate-700">BPJS Perusahaan (Info)</div>
                                        <div class="text-[11px] text-slate-400">*Ditanggung perusahaan, tidak memotong gaji</div>
                                    </td>
                                    <td class="py-2.5 px-2 text-right font-mono text-slate-600" id="row-bpjs-perusahaan">Rp 0</td>
                                </tr>
                            </tbody>
                        </table>

                        {{-- Total Pengurang Footer --}}
                        <div class="pt-3 border-t border-slate-200 flex items-center justify-between text-sm font-semibold text-slate-900">
                            <span>Total Pengurang Gaji</span>
                            <span class="font-mono text-rose-700" id="subtotal-total-pengurang">Rp 0</span>
                        </div>
                    </div>
                </div>

            </div>

            {{-- 4. Panel Asumsi Demo & Catatan Kebijakan Payroll --}}
            <div class="p-5 bg-blue-50/50 border border-blue-100 rounded-xl space-y-3">
                <div class="flex items-center gap-2 text-blue-900 font-semibold text-sm">
                    <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Asumsi Demo &amp; Aturan Perhitungan F-5</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs text-slate-600 leading-relaxed">
                    <div class="flex items-start gap-2">
                        <span class="text-blue-500 font-bold">&bull;</span>
                        <span><strong>Total Pendapatan:</strong> Bonus periode sudah termasuk dalam total pendapatan kotor (aturan konsistensi ERP F-5).</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="text-blue-500 font-bold">&bull;</span>
                        <span><strong>Pemisahan Potongan:</strong> Potongan komponen dan BPJS karyawan dicatat terpisah dan keduanya mengurangi gaji kotor.</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="text-blue-500 font-bold">&bull;</span>
                        <span><strong>BPJS Perusahaan:</strong> Iuran premi yang ditanggung perusahaan dicatat tersendiri dan tidak mengurangi nominal gaji bersih karyawan.</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="text-blue-500 font-bold">&bull;</span>
                        <span><strong>Status PPh 21:</strong> Pajak penghasilan PPh 21 belum dipotong (menunggu keputusan kebijakan pada pertanyaan terbuka Q-11).</span>
                    </div>
                </div>
            </div>

        </div>

        {{-- Footer Layar (Sesuai Konvensi UI ERP F-5) --}}
        <div class="flex items-center justify-between text-xs text-slate-400 pt-2">
            <span>Seluruh nama, tanggal, dan angka adalah data contoh.</span>
            <span>UI-K05-04</span>
        </div>

    </div>
</x-layouts.app>

