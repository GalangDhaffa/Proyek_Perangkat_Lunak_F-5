/**
 * Termin Tenaga Ahli Frontend Module - ERP PT Anaheim Nimbus Universal (Kelompok 5 F-5)
 * Mendukung FTR-K05-08 dan UI-K05-08.
 *
 * Catatan Arsitektur:
 * - Kontrak API backend untuk termin tenaga ahli belum tersedia.
 * - Modul ini menggunakan adapter demo terpisah (Shared Demo Storage di browser)
 *   dengan penanda "Mode demo" yang jelas dan transparan.
 * - Tidak mengklaim data berasal dari F-2 atau pembayaran bank benar-benar diproses.
 * - Tangani tampilan: tersedia, kosong, loading, validasi formulir, dan penanganan galat.
 */

export function formatRupiah(amount) {
    if (amount === null || amount === undefined || isNaN(amount)) {
        return 'Rp 0';
    }
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount).replace(/\s+/g, '');
}

export function formatTanggalIndo(dateStr) {
    if (!dateStr) return '—';
    try {
        const parts = dateStr.split('-');
        if (parts.length === 3) {
            const d = new Date(Date.UTC(parts[0], parts[1] - 1, parts[2]));
            return new Intl.DateTimeFormat('id-ID', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                timeZone: 'UTC',
            }).format(d);
        }
        return dateStr;
    } catch {
        return dateStr;
    }
}

// ── Data Awal Mock Bawaan ───────────────────────────────────────────────────
// Nilai TRM-2026-001 (Rp 25.000.000) konsisten dengan totalTermin pada examples/success.json
const DEFAULT_TERMIN_SEED = [
    {
        id: 'TRM-2026-001',
        periodeId: '01a1193d-2500-7c04-854b-8bf03c711dde',
        expertId: 'EXP-001',
        namaTenagaAhli: 'Laras Anindita',
        peran: 'Konsultan Arsitektur Perangkat Lunak',
        nomorKontrak: 'KTR/2026/XI/004',
        namaProyek: 'Pengembangan ERP Anaheim Nimbus',
        namaTermin: 'Termin 1 - Arsitektur & Desain Basis Data',
        deliverable: 'Dokumen Software Architecture Document (SAD) dan skema relational database ERP.',
        nominal: 25000000,
        tanggalJatuhTempo: '2026-11-20',
        tanggalBayar: '2026-11-25',
        status: 'Dibayar',
        catatanVerifikasi: 'Diverifikasi oleh Project Manager pada 2026-11-22 dan diproses pembayaran.',
    },
    {
        id: 'TRM-2026-002',
        periodeId: '01a1193d-2500-7c04-854b-8bf03c711dde',
        expertId: 'EXP-002',
        namaTenagaAhli: 'Dr. Hendra Wijaya',
        peran: 'Spesialis Keamanan Siber & Audit Sistem',
        nomorKontrak: 'KTR/2026/XI/007',
        namaProyek: 'Pengembangan ERP Anaheim Nimbus',
        namaTermin: 'Termin 1 - Audit Keamanan & Vulnerability Assessment',
        deliverable: 'Laporan penetration testing awal modul payroll dan pengerasan konfigurasi server API.',
        nominal: 15000000,
        tanggalJatuhTempo: '2026-11-28',
        tanggalBayar: null,
        status: 'Disetujui PM',
        catatanVerifikasi: 'Milestone fase 1 telah diperiksa dan disetujui PM untuk proses pencairan.',
    },
    {
        id: 'TRM-2026-003',
        periodeId: '01a1193d-2500-7c04-854b-8bf03c711dde',
        expertId: 'EXP-003',
        namaTenagaAhli: 'Maya Puspita, S.Kom.',
        peran: 'Konsultan UI/UX Design System',
        nomorKontrak: 'KTR/2026/XI/009',
        namaProyek: 'Desain Antarmuka Anaheim Nimbus ERP',
        namaTermin: 'Termin 2 - Finalisasi Prototype Figma UI-K05',
        deliverable: 'File master design system komponen UI-K05 dan alur interaktif payroll.',
        nominal: 10000000,
        tanggalJatuhTempo: '2026-12-05',
        tanggalBayar: null,
        status: 'Menunggu Verifikasi PM',
        catatanVerifikasi: 'Menunggu review hasil deliverable dari Project Manager.',
    },
];

const STORAGE_KEY = 'erpf5_demo_termin_data';

export function initTerminTenagaAhli() {
    const root = document.getElementById('termin-tenaga-ahli-app');
    if (!root) return;

    // ── Elemen DOM ──────────────────────────────────────────────────────────
    const selectPeriode           = document.getElementById('select-periode');
    const selectFilterStatus      = document.getElementById('select-filter-status');
    const inputSearch             = document.getElementById('input-search');
    const textPeriodeRentang      = document.getElementById('text-periode-rentang');

    const btnNavKePeriode         = document.getElementById('btn-nav-ke-periode');
    const btnNavKeRekap           = document.getElementById('btn-nav-ke-rekap');
    const btnBukaTambahTermin     = document.getElementById('btn-buka-tambah-termin');

    // Alerts
    const alertSukses             = document.getElementById('alert-sukses');
    const textSukses              = document.getElementById('text-sukses');
    const btnCloseAlertSukses     = document.getElementById('btn-close-alert-sukses');

    const alertError              = document.getElementById('alert-error');
    const textError               = document.getElementById('text-error');
    const btnRetryError           = document.getElementById('btn-retry-error');
    const btnCloseAlertError      = document.getElementById('btn-close-alert-error');

    // Stat Cards
    const statTotalTermin         = document.getElementById('stat-total-termin');
    const statJumlahTermin        = document.getElementById('stat-jumlah-termin');
    const statTerminDibayar       = document.getElementById('stat-termin-dibayar');
    const statTerminDibayarCount  = document.getElementById('stat-termin-dibayar-count');
    const statTerminMenunggu      = document.getElementById('stat-termin-menunggu');
    const statTerminMenungguCount = document.getElementById('stat-termin-menunggu-count');

    // States
    const stateLoading            = document.getElementById('state-loading');
    const stateEmpty              = document.getElementById('state-empty');
    const stateContent            = document.getElementById('state-content');
    const tbodyTermin             = document.getElementById('tbody-termin');
    const badgeTotalBaris         = document.getElementById('badge-total-baris');
    const btnEmptyTambah          = document.getElementById('btn-empty-tambah');
    const btnEmptyMuatDemo        = document.getElementById('btn-empty-muat-demo');

    // Modal Detail
    const modalDetail             = document.getElementById('modal-detail');
    const detailJudul             = document.getElementById('detail-judul');
    const detailKode              = document.getElementById('detail-kode');
    const detailNominal           = document.getElementById('detail-nominal');
    const detailBadgeStatus       = document.getElementById('detail-badge-status');
    const detailExpertNama        = document.getElementById('detail-expert-nama');
    const detailExpertPeran       = document.getElementById('detail-expert-peran');
    const detailExpertId          = document.getElementById('detail-expert-id');
    const detailKontrak           = document.getElementById('detail-kontrak');
    const detailProyek            = document.getElementById('detail-proyek');
    const detailJatuhTempo        = document.getElementById('detail-jatuh-tempo');
    const detailTanggalBayar      = document.getElementById('detail-tanggal-bayar');
    const detailDeliverable       = document.getElementById('detail-deliverable');
    const detailCatatanVerifikasi = document.getElementById('detail-catatan-verifikasi');
    const btnCloseModalDetail     = document.getElementById('btn-close-modal-detail');
    const btnCloseDetailSecondary = document.getElementById('btn-close-detail-secondary');
    const btnDetailEdit           = document.getElementById('btn-detail-edit');
    const btnDetailApprove        = document.getElementById('btn-detail-approve');
    const btnDetailBayar          = document.getElementById('btn-detail-bayar');

    // Modal Form
    const modalForm               = document.getElementById('modal-form');
    const formModalTitle          = document.getElementById('form-modal-title');
    const formTermin              = document.getElementById('form-termin');
    const inputFormId             = document.getElementById('input-form-id');
    const selectFormExpert        = document.getElementById('select-form-expert');
    const wrapperExpertKustom     = document.getElementById('wrapper-expert-kustom');
    const inputKustomNama         = document.getElementById('input-kustom-nama');
    const inputKustomPeran        = document.getElementById('input-kustom-peran');
    const inputFormNama           = document.getElementById('input-form-nama');
    const inputFormKontrak        = document.getElementById('input-form-kontrak');
    const inputFormProyek         = document.getElementById('input-form-proyek');
    const selectFormPeriode       = document.getElementById('select-form-periode');
    const inputFormNominal        = document.getElementById('input-form-nominal');
    const inputFormJatuhTempo     = document.getElementById('input-form-jatuh-tempo');
    const inputFormTanggalBayar   = document.getElementById('input-form-tanggal-bayar');
    const selectFormStatus        = document.getElementById('select-form-status');
    const textareaFormDeliverable = document.getElementById('textarea-form-deliverable');

    const btnBatalForm            = document.getElementById('btn-batal-form');
    const btnCloseModalForm       = document.getElementById('btn-close-modal-form');
    const btnSimpanForm           = document.getElementById('btn-simpan-form');
    const iconFormSpinner         = document.getElementById('icon-form-spinner');
    const textTombolFormSimpan    = document.getElementById('text-tombol-form-simpan');

    // Accordion Pengujian
    const btnTogglePanelUji       = document.getElementById('btn-toggle-panel-uji');
    const panelPengujian          = document.getElementById('panel-pengujian');
    const iconToggleUji           = document.getElementById('icon-toggle-uji');
    const btnUjiNormal            = document.getElementById('btn-uji-skenario-normal');
    const btnUjiKosong            = document.getElementById('btn-uji-skenario-kosong');
    const btnUjiLoading           = document.getElementById('btn-uji-simulasi-loading');
    const btnUjiError             = document.getElementById('btn-uji-simulasi-error');
    const btnUjiReset             = document.getElementById('btn-uji-reset-demo');

    // ── Internal State ──────────────────────────────────────────────────────
    let terminRecords = [];
    let initialPeriods = [];
    let activeDetailTerminId = null;

    try {
        const rawP = root.dataset.initialPeriods;
        if (rawP) initialPeriods = JSON.parse(rawP);
    } catch {
        initialPeriods = [];
    }

    // ── Demo Storage Adapter ────────────────────────────────────────────────
    function loadRecordsFromStorage() {
        try {
            const raw = localStorage.getItem(STORAGE_KEY);
            if (raw) {
                const parsed = JSON.parse(raw);
                if (Array.isArray(parsed)) {
                    return parsed;
                }
            }
        } catch (e) {
            console.warn('[termin-tenaga-ahli] Gagal membaca storage demo:', e);
        }

        // Jika belum ada di storage, simpan default seed
        saveRecordsToStorage(DEFAULT_TERMIN_SEED);
        return JSON.parse(JSON.stringify(DEFAULT_TERMIN_SEED));
    }

    function saveRecordsToStorage(records) {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(records));
        } catch (e) {
            console.warn('[termin-tenaga-ahli] Gagal menyimpan storage demo:', e);
        }
    }

    terminRecords = loadRecordsFromStorage();

    // ── Helper UI ───────────────────────────────────────────────────────────
    function show(el) { if (el) el.classList.remove('hidden'); }
    function hide(el) { if (el) el.classList.add('hidden'); }

    function hideAlerts() {
        hide(alertSukses);
        hide(alertError);
    }

    function getStatusBadgeHtml(status) {
        switch (status) {
            case 'Dibayar':
                return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Dibayar</span>';
            case 'Disetujui PM':
                return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">Disetujui PM</span>';
            case 'Menunggu Verifikasi PM':
                return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">Menunggu PM</span>';
            default:
                return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200">Draf</span>';
        }
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // ── Update URL & Nav Links ──────────────────────────────────────────────
    function updateNavigationLinks() {
        const currentPid = (selectPeriode && selectPeriode.value !== 'semua') ? selectPeriode.value : '';

        if (btnNavKePeriode) {
            btnNavKePeriode.href = currentPid ? `/periode-payroll?periodeId=${encodeURIComponent(currentPid)}` : '/periode-payroll';
        }
        if (btnNavKeRekap) {
            btnNavKeRekap.href = currentPid ? `/rekap-biaya?periodeId=${encodeURIComponent(currentPid)}` : '/rekap-biaya';
        }

        // Sinkronisasi query param periodeId di browser tanpa refresh
        if (window.history && window.history.replaceState) {
            const params = new URLSearchParams();
            if (currentPid) params.set('periodeId', currentPid);
            const paramStr = params.toString() ? `?${params.toString()}` : window.location.pathname;
            window.history.replaceState(null, '', paramStr);
        }
    }

    function updatePeriodeMeta() {
        if (!selectPeriode) return;
        const pVal = selectPeriode.value;

        if (pVal === 'semua') {
            textPeriodeRentang.textContent = 'Menampilkan seluruh periode';
        } else {
            const found = initialPeriods.find(p => (p.uuid || p.id) === pVal);
            if (found) {
                const mulai = found.tanggal_mulai || found.tanggalMulai || '—';
                const akhir = found.tanggal_akhir || found.tanggalAkhir || '—';
                textPeriodeRentang.textContent = `${mulai} s.d. ${akhir}`;
            } else {
                textPeriodeRentang.textContent = 'Periode demo terpilih';
            }
        }

        updateNavigationLinks();
    }

    // ── Filter & Render Tabel Termin ────────────────────────────────────────
    function getFilteredTermin() {
        const currentPid = selectPeriode ? selectPeriode.value : 'semua';
        const currentStatus = selectFilterStatus ? selectFilterStatus.value : 'semua';
        const searchKeyword = (inputSearch ? inputSearch.value.trim().toLowerCase() : '');

        return terminRecords.filter(item => {
            // Filter Periode
            if (currentPid !== 'semua') {
                if (item.periodeId && item.periodeId.toLowerCase() !== currentPid.toLowerCase()) {
                    return false;
                }
            }

            // Filter Status
            if (currentStatus !== 'semua') {
                if (item.status !== currentStatus) {
                    return false;
                }
            }

            // Filter Pencarian Teks
            if (searchKeyword.length > 0) {
                const matchKode = (item.id || '').toLowerCase().includes(searchKeyword);
                const matchNamaTermin = (item.namaTermin || '').toLowerCase().includes(searchKeyword);
                const matchNamaAhli = (item.namaTenagaAhli || '').toLowerCase().includes(searchKeyword);
                const matchKontrak = (item.nomorKontrak || '').toLowerCase().includes(searchKeyword);
                const matchProyek = (item.namaProyek || '').toLowerCase().includes(searchKeyword);
                if (!matchKode && !matchNamaTermin && !matchNamaAhli && !matchKontrak && !matchProyek) {
                    return false;
                }
            }

            return true;
        });
    }

    function renderTerminTable() {
        hideAlerts();
        const filtered = getFilteredTermin();

        // Hitung KPI Statistik
        let totalNominal = 0;
        let totalDibayar = 0;
        let countDibayar = 0;
        let totalMenunggu = 0;
        let countMenunggu = 0;

        filtered.forEach(item => {
            const nom = parseFloat(item.nominal) || 0;
            totalNominal += nom;

            if (item.status === 'Dibayar') {
                totalDibayar += nom;
                countDibayar++;
            } else if (item.status === 'Menunggu Verifikasi PM' || item.status === 'Draf') {
                totalMenunggu += nom;
                countMenunggu++;
            }
        });

        statTotalTermin.textContent = formatRupiah(totalNominal);
        statJumlahTermin.textContent = filtered.length;
        statTerminDibayar.textContent = formatRupiah(totalDibayar);
        statTerminDibayarCount.textContent = `${countDibayar} termin terealisasi`;
        statTerminMenunggu.textContent = formatRupiah(totalMenunggu);
        statTerminMenungguCount.textContent = `${countMenunggu} termin dalam antrean`;

        badgeTotalBaris.textContent = `${filtered.length} termin`;

        // Render Baris Tabel
        tbodyTermin.innerHTML = '';

        if (filtered.length === 0) {
            hide(stateContent);
            show(stateEmpty);
            return;
        }

        hide(stateEmpty);
        show(stateContent);

        filtered.forEach(item => {
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-slate-50/60 transition-colors group';

            tr.innerHTML = `
                <td class="px-4 py-3.5">
                    <div class="font-semibold text-slate-900">${escapeHtml(item.namaTermin)}</div>
                    <div class="text-[11px] font-mono text-slate-400 mt-0.5">${escapeHtml(item.id)}</div>
                </td>
                <td class="px-4 py-3.5">
                    <div class="font-medium text-slate-800">${escapeHtml(item.namaTenagaAhli)}</div>
                    <div class="text-[11px] text-slate-500">${escapeHtml(item.peran || 'Tenaga Ahli')}</div>
                </td>
                <td class="px-4 py-3.5">
                    <div class="font-medium text-slate-700">${escapeHtml(item.nomorKontrak || '—')}</div>
                    <div class="text-[11px] text-slate-400">${escapeHtml(item.namaProyek || '—')}</div>
                </td>
                <td class="px-4 py-3.5 text-xs text-slate-600">
                    <div><span class="text-slate-400">Tempo:</span> ${formatTanggalIndo(item.tanggalJatuhTempo)}</div>
                    ${item.tanggalBayar ? `<div class="text-emerald-700 font-medium mt-0.5"><span class="text-emerald-600/70">Bayar:</span> ${formatTanggalIndo(item.tanggalBayar)}</div>` : ''}
                </td>
                <td class="px-4 py-3.5 text-right font-mono font-bold text-slate-900">
                    ${formatRupiah(item.nominal)}
                </td>
                <td class="px-4 py-3.5 text-center">
                    ${getStatusBadgeHtml(item.status)}
                </td>
                <td class="px-4 py-3.5 text-center">
                    <div class="flex items-center justify-center gap-1.5">
                        <button
                            type="button"
                            class="btn-row-detail px-2 py-1 text-xs font-semibold text-slate-600 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-colors"
                            data-id="${item.id}"
                            title="Lihat detail termin"
                        >
                            Detail
                        </button>
                        <button
                            type="button"
                            class="btn-row-edit px-2 py-1 text-xs font-semibold text-slate-600 hover:text-amber-600 hover:bg-amber-50 rounded-md transition-colors"
                            data-id="${item.id}"
                            title="Ubah termin"
                        >
                            Ubah
                        </button>
                        <button
                            type="button"
                            class="btn-row-delete p-1 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-md transition-colors"
                            data-id="${item.id}"
                            title="Hapus termin"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                </td>
            `;

            tbodyTermin.appendChild(tr);
        });

        attachRowActionEvents();
    }

    // ── Event Baris Tabel (Detail, Ubah, Hapus) ─────────────────────────────
    function attachRowActionEvents() {
        tbodyTermin.querySelectorAll('.btn-row-detail').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.id;
                openModalDetail(id);
            });
        });

        tbodyTermin.querySelectorAll('.btn-row-edit').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.id;
                openModalForm('edit', id);
            });
        });

        tbodyTermin.querySelectorAll('.btn-row-delete').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.id;
                if (confirm(`Hapus termin ${id} dari data demo?`)) {
                    terminRecords = terminRecords.filter(t => t.id !== id);
                    saveRecordsToStorage(terminRecords);
                    renderTerminTable();
                    textSukses.textContent = `Termin ${id} berhasil dihapus dari sesi demo.`;
                    show(alertSukses);
                }
            });
        });
    }

    // ── Modal Detail Logic ──────────────────────────────────────────────────
    function openModalDetail(id) {
        const item = terminRecords.find(t => t.id === id);
        if (!item) return;

        activeDetailTerminId = id;
        detailJudul.textContent = item.namaTermin || 'Rincian Termin Tenaga Ahli';
        detailKode.textContent = item.id;
        detailNominal.textContent = formatRupiah(item.nominal);
        detailBadgeStatus.outerHTML = getStatusBadgeHtml(item.status);

        detailExpertNama.textContent = item.namaTenagaAhli || '—';
        detailExpertPeran.textContent = item.peran || 'Tenaga Ahli Konsultan';
        detailExpertId.textContent = item.expertId || 'EXP-001';

        detailKontrak.textContent = item.nomorKontrak || '—';
        detailProyek.textContent = item.namaProyek || '—';
        detailJatuhTempo.textContent = formatTanggalIndo(item.tanggalJatuhTempo);
        detailTanggalBayar.textContent = item.tanggalBayar ? formatTanggalIndo(item.tanggalBayar) : 'Belum dibayar';

        detailDeliverable.textContent = item.deliverable || 'Tidak ada catatan deliverable yang dilampirkan.';
        detailCatatanVerifikasi.textContent = item.catatanVerifikasi || 'Belum ada catatan verifikasi resmi.';

        // Tombol aksi status
        if (item.status === 'Dibayar') {
            hide(btnDetailApprove);
            hide(btnDetailBayar);
        } else if (item.status === 'Disetujui PM') {
            hide(btnDetailApprove);
            show(btnDetailBayar);
        } else {
            show(btnDetailApprove);
            show(btnDetailBayar);
        }

        show(modalDetail);
    }

    function closeModalDetail() {
        hide(modalDetail);
        activeDetailTerminId = null;
    }

    if (btnCloseModalDetail) btnCloseModalDetail.addEventListener('click', closeModalDetail);
    if (btnCloseDetailSecondary) btnCloseDetailSecondary.addEventListener('click', closeModalDetail);

    // Tombol Verifikasi PM di Modal Detail
    if (btnDetailApprove) {
        btnDetailApprove.addEventListener('click', () => {
            if (!activeDetailTerminId) return;
            const item = terminRecords.find(t => t.id === activeDetailTerminId);
            if (item) {
                item.status = 'Disetujui PM';
                item.catatanVerifikasi = `Diverifikasi dan disetujui PM pada ${new Date().toISOString().slice(0, 10)}.`;
                saveRecordsToStorage(terminRecords);
                renderTerminTable();
                closeModalDetail();
                textSukses.textContent = `Termin ${item.id} berhasil diverifikasi dengan status "Disetujui PM".`;
                show(alertSukses);
            }
        });
    }

    // Tombol Catat Pembayaran di Modal Detail
    if (btnDetailBayar) {
        btnDetailBayar.addEventListener('click', () => {
            if (!activeDetailTerminId) return;
            const item = terminRecords.find(t => t.id === activeDetailTerminId);
            if (item) {
                item.status = 'Dibayar';
                if (!item.tanggalBayar) {
                    item.tanggalBayar = new Date().toISOString().slice(0, 10);
                }
                saveRecordsToStorage(terminRecords);
                renderTerminTable();
                closeModalDetail();
                textSukses.textContent = `Pembayaran termin ${item.id} (${formatRupiah(item.nominal)}) berhasil dicatat sebagai Dibayar dalam mode demo.`;
                show(alertSukses);
            }
        });
    }

    if (btnDetailEdit) {
        btnDetailEdit.addEventListener('click', () => {
            const currentId = activeDetailTerminId;
            closeModalDetail();
            openModalForm('edit', currentId);
        });
    }

    // ── Modal Form Tambah / Edit ────────────────────────────────────────────
    function clearFormValidation() {
        [inputFormNama, inputFormKontrak, inputFormProyek, inputFormNominal, inputFormJatuhTempo].forEach(inp => {
            if (inp) inp.classList.remove('border-rose-400', 'bg-rose-50/20');
        });
        ['error-form-nama', 'error-form-kontrak', 'error-form-proyek', 'error-form-nominal', 'error-form-jatuh-tempo'].forEach(id => {
            const el = document.getElementById(id);
            if (el) { el.textContent = ''; hide(el); }
        });
    }

    function openModalForm(mode = 'create', id = null) {
        hideAlerts();
        clearFormValidation();

        if (mode === 'edit' && id) {
            const item = terminRecords.find(t => t.id === id);
            if (!item) return;

            formModalTitle.textContent = `Ubah Data Termin (${item.id})`;
            inputFormId.value = item.id;
            inputFormNama.value = item.namaTermin || '';
            inputFormKontrak.value = item.nomorKontrak || '';
            inputFormProyek.value = item.namaProyek || '';
            inputFormNominal.value = item.nominal || '';
            inputFormJatuhTempo.value = item.tanggalJatuhTempo || '';
            inputFormTanggalBayar.value = item.tanggalBayar || '';
            selectFormStatus.value = item.status || 'Draf';
            textareaFormDeliverable.value = item.deliverable || '';

            // Set expert
            const expertOpt = Array.from(selectFormExpert.options).find(o => o.value === item.expertId);
            if (expertOpt) {
                selectFormExpert.value = item.expertId;
                hide(wrapperExpertKustom);
            } else {
                selectFormExpert.value = 'CUSTOM';
                show(wrapperExpertKustom);
                inputKustomNama.value = item.namaTenagaAhli || '';
                inputKustomPeran.value = item.peran || '';
            }

            // Set periode jika ada
            if (item.periodeId && selectFormPeriode) {
                selectFormPeriode.value = item.periodeId;
            }

            textTombolFormSimpan.textContent = 'Simpan perubahan';
        } else {
            formModalTitle.textContent = 'Tambah Termin Tenaga Ahli Baru';
            inputFormId.value = '';
            inputFormNama.value = '';
            inputFormKontrak.value = 'KTR/2026/XI/004';
            inputFormProyek.value = 'ERP PT Anaheim Nimbus Universal';
            inputFormNominal.value = '25000000';
            inputFormJatuhTempo.value = '2026-11-20';
            inputFormTanggalBayar.value = '';
            selectFormStatus.value = 'Draf';
            textareaFormDeliverable.value = '';

            selectFormExpert.selectedIndex = 0;
            hide(wrapperExpertKustom);

            // Default periode sesuai dropdown
            if (selectPeriode && selectPeriode.value !== 'semua' && selectFormPeriode) {
                selectFormPeriode.value = selectPeriode.value;
            }

            textTombolFormSimpan.textContent = 'Simpan termin';
        }

        show(modalForm);
        inputFormNama.focus();
    }

    function closeModalForm() {
        hide(modalForm);
        clearFormValidation();
    }

    if (btnBukaTambahTermin) btnBukaTambahTermin.addEventListener('click', () => openModalForm('create'));
    if (btnEmptyTambah) btnEmptyTambah.addEventListener('click', () => openModalForm('create'));
    if (btnBatalForm) btnBatalForm.addEventListener('click', closeModalForm);
    if (btnCloseModalForm) btnCloseModalForm.addEventListener('click', closeModalForm);

    if (selectFormExpert) {
        selectFormExpert.addEventListener('change', () => {
            if (selectFormExpert.value === 'CUSTOM') {
                show(wrapperExpertKustom);
            } else {
                hide(wrapperExpertKustom);
            }
        });
    }

    // ── Validasi & Simpan Form Termin ───────────────────────────────────────
    function validateTerminForm() {
        clearFormValidation();
        let isValid = true;

        const nama = inputFormNama.value.trim();
        if (!nama) {
            inputFormNama.classList.add('border-rose-400', 'bg-rose-50/20');
            const err = document.getElementById('error-form-nama');
            if (err) { err.textContent = 'Nama / judul termin wajib diisi.'; show(err); }
            isValid = false;
        }

        const kontrak = inputFormKontrak.value.trim();
        if (!kontrak) {
            inputFormKontrak.classList.add('border-rose-400', 'bg-rose-50/20');
            const err = document.getElementById('error-form-kontrak');
            if (err) { err.textContent = 'Nomor kontrak wajib diisi.'; show(err); }
            isValid = false;
        }

        const nominal = parseFloat(inputFormNominal.value);
        if (isNaN(nominal) || nominal <= 0) {
            inputFormNominal.classList.add('border-rose-400', 'bg-rose-50/20');
            const err = document.getElementById('error-form-nominal');
            if (err) { err.textContent = 'Nominal termin harus berupa angka lebih dari 0.'; show(err); }
            isValid = false;
        }

        const jatuhTempo = inputFormJatuhTempo.value.trim();
        if (!jatuhTempo) {
            inputFormJatuhTempo.classList.add('border-rose-400', 'bg-rose-50/20');
            const err = document.getElementById('error-form-jatuh-tempo');
            if (err) { err.textContent = 'Tanggal jatuh tempo wajib diisi.'; show(err); }
            isValid = false;
        }

        return isValid;
    }

    if (formTermin) {
        formTermin.addEventListener('submit', (e) => {
            e.preventDefault();
            if (!validateTerminForm()) return;

            btnSimpanForm.disabled = true;
            show(iconFormSpinner);
            textTombolFormSimpan.textContent = 'Menyimpan…';

            setTimeout(() => {
                const id = inputFormId.value.trim();
                const optExpert = selectFormExpert.options[selectFormExpert.selectedIndex];

                let expertId = selectFormExpert.value;
                let namaTenagaAhli = optExpert.dataset.nama || 'Laras Anindita';
                let peran = optExpert.dataset.peran || 'Konsultan';

                if (expertId === 'CUSTOM') {
                    namaTenagaAhli = inputKustomNama.value.trim() || 'Tenaga Ahli Kustom';
                    peran = inputKustomPeran.value.trim() || 'Konsultan Spesialis';
                    expertId = `EXP-${Date.now().toString().slice(-4)}`;
                }

                const periodeId = selectFormPeriode ? selectFormPeriode.value : (selectPeriode ? selectPeriode.value : '01a1193d-2500-7c04-854b-8bf03c711dde');
                const nominal = parseFloat(inputFormNominal.value) || 0;
                const status = selectFormStatus.value;
                const tanggalBayar = inputFormTanggalBayar.value.trim() || (status === 'Dibayar' ? new Date().toISOString().slice(0, 10) : null);

                if (id) {
                    // Update
                    const idx = terminRecords.findIndex(t => t.id === id);
                    if (idx !== -1) {
                        terminRecords[idx] = {
                            ...terminRecords[idx],
                            namaTermin: inputFormNama.value.trim(),
                            nomorKontrak: inputFormKontrak.value.trim(),
                            namaProyek: inputFormProyek.value.trim(),
                            periodeId,
                            expertId,
                            namaTenagaAhli,
                            peran,
                            nominal,
                            tanggalJatuhTempo: inputFormJatuhTempo.value.trim(),
                            tanggalBayar,
                            status,
                            deliverable: textareaFormDeliverable.value.trim(),
                        };
                    }
                } else {
                    // Create New
                    const newId = `TRM-2026-${String(terminRecords.length + 1).padStart(3, '0')}`;
                    terminRecords.push({
                        id: newId,
                        periodeId,
                        expertId,
                        namaTenagaAhli,
                        peran,
                        nomorKontrak: inputFormKontrak.value.trim(),
                        namaProyek: inputFormProyek.value.trim(),
                        namaTermin: inputFormNama.value.trim(),
                        deliverable: textareaFormDeliverable.value.trim(),
                        nominal,
                        tanggalJatuhTempo: inputFormJatuhTempo.value.trim(),
                        tanggalBayar,
                        status,
                        catatanVerifikasi: status === 'Disetujui PM' ? 'Diverifikasi saat pembuatan termin.' : 'Draf termin baru.',
                    });
                }

                saveRecordsToStorage(terminRecords);
                renderTerminTable();

                btnSimpanForm.disabled = false;
                hide(iconFormSpinner);
                closeModalForm();

                textSukses.textContent = `Data termin tenaga ahli berhasil disimpan (Sesi Demo).`;
                show(alertSukses);
                alertSukses.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }, 300);
        });
    }

    // ── Penanganan Galat Server ─────────────────────────────────────────────
    function handleSimulatedError() {
        hide(stateLoading);
        hide(stateContent);
        hide(stateEmpty);

        // Catat error teknis ke konsol pengembang
        console.error('[termin-tenaga-ahli] Simulated Error: 500 INTERNAL_SERVER_ERROR in TerminTenagaAhliAdapter. Endpoint REST API belum tersedia.');

        textError.textContent = 'Terjadi kendala pada server saat memproses data termin tenaga ahli. Silakan coba beberapa saat lagi.';
        show(alertError);
        alertError.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    if (btnRetryError) {
        btnRetryError.addEventListener('click', () => {
            hide(alertError);
            renderTerminTable();
        });
    }

    if (btnCloseAlertSukses) btnCloseAlertSukses.addEventListener('click', () => hide(alertSukses));
    if (btnCloseAlertError) btnCloseAlertError.addEventListener('click', () => hide(alertError));

    // ── Event Filter & Pencarian ────────────────────────────────────────────
    if (selectPeriode) {
        selectPeriode.addEventListener('change', () => {
            updatePeriodeMeta();
            renderTerminTable();
        });
    }

    if (selectFilterStatus) {
        selectFilterStatus.addEventListener('change', renderTerminTable);
    }

    if (inputSearch) {
        inputSearch.addEventListener('input', renderTerminTable);
    }

    // ── Panel Pengujian Demo (Accordion) ────────────────────────────────────
    if (btnTogglePanelUji && panelPengujian) {
        btnTogglePanelUji.addEventListener('click', () => {
            const isCurrentlyOpen = !panelPengujian.classList.contains('hidden');
            panelPengujian.classList.toggle('hidden', isCurrentlyOpen);
            btnTogglePanelUji.setAttribute('aria-expanded', String(!isCurrentlyOpen));
            if (iconToggleUji) {
                iconToggleUji.style.transform = isCurrentlyOpen ? '' : 'rotate(90deg)';
            }
        });
    }

    // 1. Skenario Normal (3 Termin Bawaan)
    if (btnUjiNormal) {
        btnUjiNormal.addEventListener('click', () => {
            terminRecords = JSON.parse(JSON.stringify(DEFAULT_TERMIN_SEED));
            saveRecordsToStorage(terminRecords);
            if (selectFilterStatus) selectFilterStatus.value = 'semua';
            if (inputSearch) inputSearch.value = '';
            renderTerminTable();
            textSukses.textContent = 'Skenario Normal berhasil dimuat (3 termin demo).';
            show(alertSukses);
        });
    }

    // 2. Skenario Kosong
    if (btnUjiKosong) {
        btnUjiKosong.addEventListener('click', () => {
            terminRecords = [];
            saveRecordsToStorage(terminRecords);
            renderTerminTable();
            textSukses.textContent = 'Skenario Kosong berhasil diterapkan (0 termin).';
            show(alertSukses);
        });
    }

    // 3. Simulasi Loading
    if (btnUjiLoading) {
        btnUjiLoading.addEventListener('click', () => {
            hideAlerts();
            hide(stateContent);
            hide(stateEmpty);
            show(stateLoading);

            setTimeout(() => {
                hide(stateLoading);
                renderTerminTable();
            }, 800);
        });
    }

    // 4. Simulasi Galat 500
    if (btnUjiError) {
        btnUjiError.addEventListener('click', () => {
            hideAlerts();
            handleSimulatedError();
        });
    }

    // 5. Reset Demo Data
    if (btnUjiReset) {
        btnUjiReset.addEventListener('click', () => {
            localStorage.removeItem(STORAGE_KEY);
            terminRecords = loadRecordsFromStorage();
            if (selectFilterStatus) selectFilterStatus.value = 'semua';
            if (inputSearch) inputSearch.value = '';
            renderTerminTable();
            textSukses.textContent = 'Data demo termin tenaga ahli berhasil di-reset ke nilai default.';
            show(alertSukses);
        });
    }

    if (btnEmptyMuatDemo) {
        btnEmptyMuatDemo.addEventListener('click', () => {
            terminRecords = JSON.parse(JSON.stringify(DEFAULT_TERMIN_SEED));
            saveRecordsToStorage(terminRecords);
            renderTerminTable();
        });
    }

    // ── Inisialisasi Pertama Kali ───────────────────────────────────────────
    updatePeriodeMeta();
    renderTerminTable();
}

// Jalankan otomatis saat DOM siap
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTerminTenagaAhli);
} else {
    initTerminTenagaAhli();
}

