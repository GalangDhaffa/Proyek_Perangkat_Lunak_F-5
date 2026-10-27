<x-layouts.app>
    <x-slot:title>Periode Payroll - Universal ERP</x-slot:title>
    <x-slot:sessionUser>Petugas payroll &middot; Sesi demo</x-slot:sessionUser>
    <x-slot:breadcrumb><span id="breadcrumb-title">Periode payroll</span></x-slot:breadcrumb>
    <x-slot:pageTitle><span id="page-title">Periode payroll</span></x-slot:pageTitle>
    <x-slot:pageSubtitle><span id="page-subtitle">Kelola periode dan siapkan data sebelum menghitung gaji.</span></x-slot:pageSubtitle>

    <x-slot:headerActions>
        <button
            id="btn-tambah-periode"
            type="button"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 active:bg-blue-800 transition-colors shadow-xs"
        >
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span>Periode baru</span>
        </button>
    </x-slot:headerActions>

    <div id="periode-payroll-app" class="space-y-6" data-initial-periods='@json($periods ?? [])'>

        {{-- Banner Pesan Sukses --}}
        <div
            id="alert-sukses"
            class="hidden p-4 text-sm font-medium text-emerald-800 bg-emerald-50 border border-emerald-200 rounded-lg items-center gap-3 transition-all"
            role="status"
            aria-live="polite"
        >
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span id="text-sukses">Periode berhasil disimpan.</span>
        </div>

        {{-- Banner Pesan Error Umum --}}
        <div
            id="alert-error"
            class="hidden p-4 text-sm font-medium text-rose-800 bg-rose-50 border border-rose-200 rounded-lg items-center gap-3 transition-all"
            role="alert"
        >
            <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span id="text-error">Terjadi kesalahan saat memproses data. Silakan periksa kembali formulir.</span>
        </div>

        {{-- Selector Pemilihan Periode (Hanya tampil pada Mode View) --}}
        <div id="wrapper-picker-periode" class="space-y-1.5 max-w-xs">
            <label for="select-periode" class="block text-xs font-semibold text-slate-700">
                Periode
            </label>
            <div class="relative">
                <select
                    id="select-periode"
                    class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-sm text-slate-800 focus:outline-hidden focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors cursor-pointer shadow-xs"
                >
                    @forelse($periods ?? [] as $p)
                        <option value="{{ $p->uuid ?: $p->id }}" {{ $loop->first ? 'selected' : '' }}>
                            {{ $p->nama_periode ?: \App\Models\PayrollPeriod::generateNamaPeriode($p->tanggal_mulai) }}
                        </option>
                    @empty
                        <option value="" disabled selected>Belum ada periode tersimpan</option>
                    @endforelse
                </select>
            </div>
        </div>

        {{-- Form / Panel Periode --}}
        <form id="form-periode" novalidate>
            <div class="bg-white rounded-xl border border-slate-200/90 shadow-xs overflow-hidden">

                {{-- Header Panel --}}
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h2 id="panel-title" class="text-base font-semibold text-slate-900 tracking-tight">
                        Data periode
                    </h2>
                    <span id="panel-badge-status" class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                        Draf
                    </span>
                </div>

                {{-- Form Fields (3 Kolom Sesuai Desain) --}}
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                        {{-- Kolom 1: Tanggal Mulai --}}
                        <div class="space-y-1.5">
                            <label for="input-tanggal-mulai" class="block text-sm font-medium text-slate-700">
                                Tanggal mulai
                            </label>
                            <input
                                type="date"
                                id="input-tanggal-mulai"
                                name="tanggalMulai"
                                class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 transition-colors focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                readonly
                                aria-describedby="error-tanggal-mulai"
                            >
                            <p id="error-tanggal-mulai" class="hidden text-xs text-rose-600 font-medium mt-1"></p>
                        </div>

                        {{-- Kolom 2: Tanggal Akhir --}}
                        <div class="space-y-1.5">
                            <label for="input-tanggal-akhir" class="block text-sm font-medium text-slate-700">
                                Tanggal akhir
                            </label>
                            <input
                                type="date"
                                id="input-tanggal-akhir"
                                name="tanggalAkhir"
                                class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 transition-colors focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                readonly
                                aria-describedby="error-tanggal-akhir"
                            >
                            <p id="error-tanggal-akhir" class="hidden text-xs text-rose-600 font-medium mt-1"></p>
                        </div>

                        {{-- Kolom 3: Tanggal Pembayaran --}}
                        <div class="space-y-1.5">
                            <label for="input-tanggal-pembayaran" class="block text-sm font-medium text-slate-700">
                                Tanggal pembayaran
                            </label>
                            <input
                                type="date"
                                id="input-tanggal-pembayaran"
                                name="tanggalPembayaran"
                                class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 transition-colors focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                readonly
                                aria-describedby="error-tanggal-pembayaran"
                            >
                            <p id="error-tanggal-pembayaran" class="hidden text-xs text-rose-600 font-medium mt-1"></p>
                        </div>

                    </div>
                </div>

                {{-- Action Footer --}}
                <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <p id="panel-keterangan" class="text-xs text-slate-500">
                        Periode tersimpan dengan status Draf.
                    </p>

                    <div class="flex items-center gap-3">
                        {{-- Tombol Batal --}}
                        <button
                            id="btn-batal"
                            type="button"
                            class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 focus:outline-hidden focus:ring-2 focus:ring-blue-500 transition-colors shadow-2xs"
                            style="display: none;"
                        >
                            Batal
                        </button>

                        {{-- Tombol Ubah Periode (Mode View) --}}
                        <button
                            id="btn-ubah-periode"
                            type="button"
                            class="px-4 py-2 text-sm font-semibold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 focus:outline-hidden focus:ring-2 focus:ring-blue-500 transition-colors shadow-2xs"
                        >
                            Ubah periode
                        </button>

                        {{-- Tombol Simpan (Mode Create / Edit) --}}
                        <button
                            id="btn-simpan-periode"
                            type="submit"
                            class="items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 active:bg-blue-800 focus:outline-hidden focus:ring-2 focus:ring-blue-500 disabled:opacity-60 disabled:cursor-wait transition-colors shadow-xs"
                            style="display: none;"
                        >
                            <svg id="icon-spinner" class="hidden w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span id="text-tombol-simpan">Simpan periode</span>
                        </button>
                    </div>
                </div>

            </div>
        </form>

        {{-- Footer Layar (Sesuai SVG UI-K05-01) --}}
        <div class="flex items-center justify-between text-xs text-slate-400 pt-2">
            <span>Seluruh nama, tanggal, dan angka adalah data contoh.</span>
            <span>UI-K05-01</span>
        </div>

    </div>
</x-layouts.app>
