<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Rekap Biaya' }} - ERP Anaheim Nimbus</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex flex-col font-sans text-slate-800 antialiased selection:bg-blue-500 selection:text-white">

    {{-- Top Bar: Penanda Mode Demo --}}
    <header class="bg-blue-50/70 border-b border-blue-100/80 text-xs px-6 py-2 flex flex-wrap items-center justify-between gap-3 shrink-0">
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center gap-1.5 font-semibold text-blue-900">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                Data demo &mdash; bukan data nyata
            </span>
        </div>
        <div class="flex items-center gap-2 text-slate-500">
            <span>{{ $sessionUser ?? 'Manajemen · Sesi demo' }}</span>
        </div>
    </header>

    {{-- App Container --}}
    <div class="flex flex-1 overflow-hidden">

        {{-- Sidebar (Sesuai Figma Anaheim Nimbus) --}}
        <aside class="w-64 bg-white border-r border-slate-200/90 flex flex-col justify-between shrink-0">
            <div>
                {{-- Brand Header --}}
                <div class="p-6 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-blue-600 text-white font-bold text-lg flex items-center justify-center shadow-xs">
                            A
                        </div>
                        <div>
                            <div class="font-bold text-slate-900 text-sm leading-tight tracking-tight">Anaheim Nimbus</div>
                            <div class="text-[10px] uppercase font-semibold text-slate-400 tracking-wider">UNIVERSAL &middot; ERP</div>
                        </div>
                    </div>
                </div>

                {{-- Navigation --}}
                <div class="p-4">
                    <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-3 mb-2">
                        Payroll &amp; Pembayaran
                    </div>
                    <nav class="space-y-1">
                        <a href="#" class="flex items-center px-3 py-2 text-sm font-medium text-slate-600 rounded-lg hover:bg-slate-50 hover:text-slate-900 transition-colors">
                            Termin tenaga ahli
                        </a>
                        <a href="{{ url('/rekap-biaya') }}" class="flex items-center px-3 py-2 text-sm font-semibold text-blue-700 bg-blue-50/80 rounded-lg transition-colors">
                            Rekap biaya
                        </a>
                    </nav>
                </div>
            </div>

            {{-- Sidebar Footer --}}
            <div class="p-4 border-t border-slate-100 text-xs text-slate-400">
                Kelompok 5 &middot; Kelas F
            </div>
        </aside>

        {{-- Main Content Area --}}
        <main class="flex-1 overflow-y-auto flex flex-col justify-between">
            <div class="p-8 max-w-6xl mx-auto w-full">
                {{-- Breadcrumb --}}
                <div class="text-xs text-slate-500 mb-3 flex items-center gap-1.5 font-medium">
                    <span>Payroll &amp; pembayaran</span>
                    <span>/</span>
                    <span class="text-slate-700">{{ $breadcrumb ?? 'Rekap biaya' }}</span>
                </div>

                {{-- Page Header --}}
                <div class="flex items-start justify-between mb-8">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">{{ $pageTitle ?? 'Rekap biaya' }}</h1>
                        <p class="text-sm text-slate-500 mt-1">{{ $pageSubtitle ?? 'Tinjau biaya payroll dan pembayaran tenaga ahli per periode.' }}</p>
                    </div>
                    @if(isset($headerActions))
                        <div class="flex items-center gap-3">
                            {{ $headerActions }}
                        </div>
                    @endif
                </div>

                {{-- Page Body --}}
                {{ $slot }}
            </div>

            {{-- Footer --}}
            <footer class="mt-auto border-t border-slate-200/80 bg-white/60 px-8 py-3 text-xs text-slate-400 flex items-center justify-between">
                <div>Kelompok 5 &middot; Kelas F</div>
                <div>Seluruh nama, tanggal, dan angka adalah data contoh.</div>
            </footer>
        </main>
    </div>

    @stack('scripts')
</body>
</html>
