/**
 * Pratinjau Perhitungan Frontend Module - ERP PT Anaheim Nimbus Universal (Kelompok 5 F-5)
 * Mendukung FTR-K05-04 dan UI-K05-04.
 *
 * Catatan Arsitektur:
 * - Menghubungi API backend payroll jika endpoint telah tersedia.
 * - Jika API backend belum tersedia (HTTP 404/503), modul ini menggunakan dataset demo
 *   terstandar dengan penanda demo yang jelas dan transparan.
 * - Mendukung pelacakan status hasil lama (stale) saat pilihan input periode/karyawan diubah.
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

export function formatWaktuSekarang() {
    const now = new Date();
    const tgl = now.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
    const jam = String(now.getHours()).padStart(2, '0');
    const mnt = String(now.getMinutes()).padStart(2, '0');
    const dtk = String(now.getSeconds()).padStart(2, '0');
    return `${tgl}, ${jam}:${mnt}:${dtk} WIB`;
}

// Data Master Karyawan Demo (Sinkron dengan komponen-gaji.js dan dokumen acuan F-5)
// Catatan: EMP-001 adalah referensi demo cuti F-4. Jangan mengklaim referensi F-2 sebelum pemetaan dikonfirmasi.
// Placeholder jabatan dan departemen menggunakan "Belum tersedia".
const DEMO_EMPLOYEES_MAP = {
    'EMP-001': {
        id: 'EMP-001',
        nama: 'EMP-001 (Referensi demo cuti F-4)',
        jabatan: 'Belum tersedia',
        departemen: 'Belum tersedia',
        gajiPokok: 7000000,
        bonus: 750000,
        bpjsKaryawan: 65000,
        bpjsPerusahaan: 260000,
        komponen: [
            { id: 'k-1', nama: 'Potongan Keterlambatan', jenis: 'potongan', nominal: 200000, aktif: true },
        ],
    },
    'EMP-002': {
        id: 'EMP-002',
        nama: 'EMP-002',
        jabatan: 'Belum tersedia',
        departemen: 'Belum tersedia',
        gajiPokok: 6500000,
        bonus: 500000,
        bpjsKaryawan: 65000,
        bpjsPerusahaan: 260000,
        komponen: [
            { id: 'k-2', nama: 'Tunjangan Komunikasi', jenis: 'pendapatan', nominal: 250000, aktif: true },
            { id: 'k-3', nama: 'Iuran Koperasi', jenis: 'potongan', nominal: 100000, aktif: true },
        ],
    },
    'EMP-003': {
        id: 'EMP-003',
        nama: 'EMP-003',
        jabatan: 'Belum tersedia',
        departemen: 'Belum tersedia',
        gajiPokok: 8000000,
        bonus: 0,
        bpjsKaryawan: 80000,
        bpjsPerusahaan: 320000,
        komponen: [
            { id: 'k-4', nama: 'Tunjangan On-Call', jenis: 'pendapatan', nominal: 600000, aktif: true },
        ],
    },
    'EMP-999': {
        id: 'EMP-999',
        nama: 'EMP-999 (Karyawan Baru)',
        jabatan: 'Belum tersedia',
        departemen: 'Belum tersedia',
        gajiPokok: 0,
        bonus: 0,
        bpjsKaryawan: 0,
        bpjsPerusahaan: 0,
        komponen: [],
    },
};

export function initPratinjauPerhitungan() {
    const root = document.getElementById('pratinjau-perhitungan-app');
    if (!root) return;

    // ── Elemen DOM Utama ────────────────────────────────────────────────────
    const selectPeriode           = document.getElementById('select-periode');
    const selectKaryawan          = document.getElementById('select-karyawan');
    const textPeriodeRentang      = document.getElementById('text-periode-rentang');
    const badgePeriodeStatus      = document.getElementById('badge-periode-status');
    const textKaryawanDept        = document.getElementById('text-karyawan-departemen');
    const textKaryawanJabatan     = document.getElementById('text-karyawan-jabatan');

    // Navigasi
    const btnNavKeKomponen        = document.getElementById('btn-nav-ke-komponen');
    const btnNavKePeriode         = document.getElementById('btn-nav-ke-periode');
    const linkUbahKomponen        = document.getElementById('link-ubah-komponen');
    const btnKeAturKomponen       = document.getElementById('btn-ke-atur-komponen');

    // Tombol Hitung
    const btnHitungPratinjau      = document.getElementById('btn-hitung-pratinjau');
    const iconHitungSpinner       = document.getElementById('icon-hitung-spinner');
    const iconHitungCalc          = document.getElementById('icon-hitung-calc');
    const textTombolHitung        = document.getElementById('text-tombol-hitung');
    const badgeStatusKalkulasi    = document.getElementById('badge-status-kalkulasi');

    // Alerts
    const alertSukses             = document.getElementById('alert-sukses');
    const textSukses              = document.getElementById('text-sukses');
    const btnCloseAlertSukses     = document.getElementById('btn-close-alert-sukses');

    const alertStale              = document.getElementById('alert-stale');
    const btnRehitungCepat        = document.getElementById('btn-rehitung-cepat');

    const alertError              = document.getElementById('alert-error');
    const textError               = document.getElementById('text-error');
    const btnRetryError           = document.getElementById('btn-retry-error');
    const btnCloseAlertError      = document.getElementById('btn-close-alert-error');

    // State Containers
    const stateLoading            = document.getElementById('state-loading');
    const stateUnconfigured       = document.getElementById('state-unconfigured');
    const stateContent            = document.getElementById('state-content');

    // Elemen Hasil Perhitungan
    const viewKaryawanNama        = document.getElementById('view-karyawan-nama');
    const viewBadgeStatusHasil    = document.getElementById('view-badge-status-hasil');
    const viewKaryawanId          = document.getElementById('view-karyawan-id');
    const viewKaryawanJabatan     = document.getElementById('view-karyawan-jabatan');
    const viewKaryawanDept        = document.getElementById('view-karyawan-dept');
    const viewWaktuHitung         = document.getElementById('view-waktu-hitung');
    const viewSumberData          = document.getElementById('view-sumber-data');

    // Stat Cards
    const statTotalPendapatan     = document.getElementById('stat-total-pendapatan');
    const statTotalPotongan       = document.getElementById('stat-total-potongan');
    const statBpjsKaryawan        = document.getElementById('stat-bpjs-karyawan');
    const statGajiBersih          = document.getElementById('stat-gaji-bersih');

    // Detail Baris
    const rowGajiPokok            = document.getElementById('row-gaji-pokok');
    const rowBonus                = document.getElementById('row-bonus');
    const tbodyKomponenPendapatan = document.getElementById('tbody-komponen-pendapatan');
    const subtotalPendapatan      = document.getElementById('subtotal-pendapatan');
    const subtotalPendapatanHeader= document.getElementById('subtotal-pendapatan-header');

    const tbodyKomponenPotongan   = document.getElementById('tbody-komponen-potongan');
    const rowBpjsKaryawan         = document.getElementById('row-bpjs-karyawan');
    const rowBpjsPerusahaan       = document.getElementById('row-bpjs-perusahaan');
    const subtotalTotalPengurang  = document.getElementById('subtotal-total-pengurang');
    const subtotalPotonganHeader  = document.getElementById('subtotal-potongan-header');

    // Accordion Pengujian
    const btnTogglePanelUji       = document.getElementById('btn-toggle-panel-uji');
    const panelPengujian          = document.getElementById('panel-pengujian');
    const iconToggleUji           = document.getElementById('icon-toggle-uji');
    const btnUjiHitungNormal      = document.getElementById('btn-uji-hitung-normal');
    const btnUjiTanpaKonfigurasi  = document.getElementById('btn-uji-tanpa-konfigurasi');
    const btnUjiSimulasiGagal     = document.getElementById('btn-uji-simulasi-gagal');
    const btnUjiTandaiStale       = document.getElementById('btn-uji-tandai-stale');

    // ── Internal State Tracking ─────────────────────────────────────────────
    let hasCalculated = false;
    let lastCalculatedPeriodeId = null;
    let lastCalculatedEmployeeId = null;
    let isStale = false;

    // Baca data awal dari atribut root
    let initialPeriods = [];
    try {
        const raw = root.dataset.initialPeriods;
        if (raw) initialPeriods = JSON.parse(raw);
    } catch {
        initialPeriods = [];
    }

    const selectedPeriodeIdFromAttr = root.dataset.selectedPeriodeId;
    if (selectedPeriodeIdFromAttr && selectPeriode) {
        const match = Array.from(selectPeriode.options).find(
            opt => opt.value.toLowerCase() === selectedPeriodeIdFromAttr.toLowerCase()
        );
        if (match) selectPeriode.value = match.value;
    }

    const selectedEmployeeIdFromAttr = root.dataset.selectedEmployeeId;
    if (selectedEmployeeIdFromAttr && selectKaryawan) {
        const empMatch = Array.from(selectKaryawan.options).find(
            opt => opt.value.toLowerCase() === selectedEmployeeIdFromAttr.toLowerCase()
        );
        if (empMatch) selectKaryawan.value = empMatch.value;
    }

    // ── Helper Tampilan ─────────────────────────────────────────────────────
    function show(el) {
        if (!el) return;
        el.classList.remove('hidden');
    }

    function hide(el) {
        if (!el) return;
        el.classList.add('hidden');
    }

    function hideAllAlerts() {
        hide(alertSukses);
        hide(alertStale);
        hide(alertError);
    }

    // ── Penyimpanan Demo Bersama (Shared Demo Storage) ────────────────────────
    // Menghubungkan data simpan Komponen Gaji dengan Pratinjau berdasarkan periodeId dan employeeId
    const DEMO_STORAGE_PREFIX = 'erpf5_demo_komponen_';
    const DEMO_STALE_PREFIX   = 'erpf5_demo_stale_';

    function getSharedStorageKey(periodeId, employeeId) {
        return `${DEMO_STORAGE_PREFIX}${periodeId || 'default'}_${employeeId || 'EMP-001'}`;
    }

    function getSharedStaleKey(periodeId, employeeId) {
        return `${DEMO_STALE_PREFIX}${periodeId || 'default'}_${employeeId || 'EMP-001'}`;
    }

    /**
     * Mengambil data komponen gaji karyawan.
     * Mengutamakan data yang disimpan di penyimpanan demo bersama (localStorage).
     * Jangan mengganti input tersimpan dengan fixture bawaan!
     */
    function getEmployeeComponentData(periodeId, employeeId) {
        const sharedKey = getSharedStorageKey(periodeId, employeeId);
        try {
            const savedRaw = localStorage.getItem(sharedKey);
            if (savedRaw) {
                const parsed = JSON.parse(savedRaw);
                parsed.id = employeeId;
                if (!parsed.nama) {
                    parsed.nama = (DEMO_EMPLOYEES_MAP[employeeId]?.nama) || employeeId;
                }
                if (!parsed.jabatan) parsed.jabatan = 'Belum tersedia';
                if (!parsed.departemen) parsed.departemen = 'Belum tersedia';
                return { data: parsed, isStored: true };
            }
        } catch (e) {
            console.warn('[pratinjau-perhitungan] Gagal membaca shared demo storage:', e);
        }

        const base = DEMO_EMPLOYEES_MAP[employeeId] || DEMO_EMPLOYEES_MAP['EMP-001'];
        return { data: JSON.parse(JSON.stringify(base)), isStored: false };
    }

    // ── Sinkronisasi Link Navigasi ──────────────────────────────────────────
    function updateNavigationLinks() {
        const currentPid = selectPeriode ? selectPeriode.value : '';
        const currentEmpId = selectKaryawan ? selectKaryawan.value : '';

        const params = new URLSearchParams();
        if (currentPid) params.set('periodeId', currentPid);
        if (currentEmpId) params.set('employeeId', currentEmpId);

        const paramString = params.toString() ? '?' + params.toString() : '';

        if (btnNavKeKomponen) {
            btnNavKeKomponen.href = `/komponen-gaji${paramString}`;
        }
        if (linkUbahKomponen) {
            linkUbahKomponen.href = `/komponen-gaji${paramString}`;
        }
        if (btnKeAturKomponen) {
            btnKeAturKomponen.href = `/komponen-gaji${paramString}`;
        }
        if (btnNavKePeriode) {
            btnNavKePeriode.href = currentPid ? `/periode-payroll?periodeId=${encodeURIComponent(currentPid)}` : `/periode-payroll`;
        }

        // Sinkronkan parameter query di address bar browser tanpa reload
        if (window.history && window.history.replaceState && paramString) {
            const newUrl = `${window.location.pathname}${paramString}`;
            window.history.replaceState(null, '', newUrl);
        }
    }

    // ── Perbarui Metadata Periode & Karyawan ────────────────────────────────
    function updateSelectedMeta() {
        if (!selectPeriode || !selectKaryawan) return;

        const pVal = selectPeriode.value;
        const foundPeriod = initialPeriods.find(p => (p.uuid || p.id) === pVal);
        if (foundPeriod) {
            const mulai = foundPeriod.tanggal_mulai || foundPeriod.tanggalMulai || '—';
            const akhir = foundPeriod.tanggal_akhir || foundPeriod.tanggalAkhir || '—';
            textPeriodeRentang.textContent = `${mulai} s.d. ${akhir}`;
            badgePeriodeStatus.textContent = foundPeriod.status || 'Draf';
        } else {
            textPeriodeRentang.textContent = '01 Okt 2026 s.d. 31 Okt 2026';
            badgePeriodeStatus.textContent = 'Draf';
        }

        const currentPid = selectPeriode.value;
        const currentEmpId = selectKaryawan.value;
        const { data: empData } = getEmployeeComponentData(currentPid, currentEmpId);

        textKaryawanDept.textContent = empData.departemen || 'Belum tersedia';
        textKaryawanJabatan.textContent = empData.jabatan || 'Belum tersedia';

        updateNavigationLinks();
        checkStaleCondition();
    }

    // ── Periksa Apakah Hasil Menjadi Stale (Perlu Hitung Ulang) ──────────────
    function isDataMarkedStale(pid, empId) {
        try {
            if (localStorage.getItem(getSharedStaleKey(pid, empId)) === 'true') {
                return true;
            }
        } catch (e) {}

        if (hasCalculated) {
            if (pid !== lastCalculatedPeriodeId || empId !== lastCalculatedEmployeeId) {
                return true;
            }
        }

        return false;
    }

    function checkStaleCondition() {
        const currentPid = selectPeriode.value;
        const currentEmpId = selectKaryawan.value;

        if (isDataMarkedStale(currentPid, currentEmpId)) {
            isStale = true;
            show(alertStale);
            hide(alertSukses);

            badgeStatusKalkulasi.textContent = 'Perlu dihitung ulang';
            badgeStatusKalkulasi.className = 'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-300';

            viewBadgeStatusHasil.textContent = 'Perlu Dihitung Ulang (Stale)';
            viewBadgeStatusHasil.className = 'px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-300';

            if (hasCalculated) {
                stateContent.classList.add('opacity-75');
            }
        } else if (hasCalculated) {
            isStale = false;
            hide(alertStale);

            badgeStatusKalkulasi.textContent = 'Hasil mutakhir';
            badgeStatusKalkulasi.className = 'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-300';

            viewBadgeStatusHasil.textContent = 'Hasil Mutakhir';
            viewBadgeStatusHasil.className = 'px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200';

            stateContent.classList.remove('opacity-75');
        }
    }

    // ── Render Rincian Kalkulasi ────────────────────────────────────────────
    function renderCalculationData(emp, calculationSource = 'Mode Demo (Penyimpanan Bersama)') {
        // Tampilkan info karyawan
        viewKaryawanNama.textContent = emp.nama;
        viewKaryawanId.textContent = emp.id;
        viewKaryawanJabatan.textContent = emp.jabatan || 'Belum tersedia';
        viewKaryawanDept.textContent = emp.departemen || 'Belum tersedia';
        viewWaktuHitung.textContent = formatWaktuSekarang();
        viewSumberData.textContent = calculationSource;

        const gajiPokok = emp.gajiPokok || 0;
        const bonus = emp.bonus || 0;
        const bpjsKaryawan = emp.bpjsKaryawan || 0;
        const bpjsPerusahaan = emp.bpjsPerusahaan || 0;

        let totalKomponenPendapatan = 0;
        let totalKomponenPotongan = 0;

        tbodyKomponenPendapatan.innerHTML = '';
        tbodyKomponenPotongan.innerHTML = '';

        (emp.komponen || []).forEach(k => {
            if (k.aktif !== false) {
                const tr = document.createElement('tr');
                if (k.jenis === 'pendapatan') {
                    totalKomponenPendapatan += (k.nominal || 0);
                    tr.innerHTML = `
                        <td class="py-2.5">
                            <div class="font-medium text-slate-800">${escapeHtml(k.nama)}</div>
                            <div class="text-[11px] text-emerald-600 font-medium">+ Komponen Pendapatan</div>
                        </td>
                        <td class="py-2.5 text-right font-mono text-slate-900">${formatRupiah(k.nominal)}</td>
                    `;
                    tbodyKomponenPendapatan.appendChild(tr);
                } else {
                    totalKomponenPotongan += (k.nominal || 0);
                    tr.innerHTML = `
                        <td class="py-2.5">
                            <div class="font-medium text-slate-800">${escapeHtml(k.nama)}</div>
                            <div class="text-[11px] text-rose-600 font-medium">&minus; Potongan Komponen</div>
                        </td>
                        <td class="py-2.5 text-right font-mono text-rose-600">${formatRupiah(k.nominal)}</td>
                    `;
                    tbodyKomponenPotongan.appendChild(tr);
                }
            }
        });

        // Rumus Aturan ERP F-5:
        // Total Pendapatan = Gaji Pokok + Bonus + Total Komponen Pendapatan Aktif
        // Total Pengurang Gaji = Total Komponen Potongan Aktif + BPJS Karyawan
        // Estimasi Gaji Bersih = Total Pendapatan - Total Pengurang Gaji
        // BPJS Perusahaan: ditanggung perusahaan, TIDAK mengurangi gaji bersih karyawan
        const totalPendapatan = gajiPokok + bonus + totalKomponenPendapatan;
        const totalPotonganKaryawan = totalKomponenPotongan + bpjsKaryawan;
        const gajiBersih = totalPendapatan - totalPotonganKaryawan;

        // Set Nilai Baris Detail
        rowGajiPokok.textContent = formatRupiah(gajiPokok);
        rowBonus.textContent = formatRupiah(bonus);
        subtotalPendapatan.textContent = formatRupiah(totalPendapatan);
        subtotalPendapatanHeader.textContent = formatRupiah(totalPendapatan);

        rowBpjsKaryawan.textContent = formatRupiah(bpjsKaryawan);
        rowBpjsPerusahaan.textContent = formatRupiah(bpjsPerusahaan);
        subtotalTotalPengurang.textContent = formatRupiah(totalPotonganKaryawan);
        subtotalPotonganHeader.textContent = formatRupiah(totalPotonganKaryawan);

        // Set Stat Cards
        statTotalPendapatan.textContent = formatRupiah(totalPendapatan);
        statTotalPotongan.textContent = formatRupiah(totalKomponenPotongan);
        statBpjsKaryawan.textContent = formatRupiah(bpjsKaryawan);
        statGajiBersih.textContent = formatRupiah(gajiBersih);
    }

    function escapeHtml(str) {
        if (!str) return '';
        return str
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // ── Penanganan Galat Server (HTTP 500 / Error Ramah Pengguna) ────────────
    function handleServerError(statusCode = 500, developerLogMessage = '') {
        hideAllAlerts();
        hide(stateLoading);
        hide(stateContent);
        hide(stateUnconfigured);

        // Simpan detail exception/SQL ke log browser pengembang
        console.error(`[pratinjau-perhitungan] Server error HTTP ${statusCode}:`, developerLogMessage);

        const errorBadge = document.getElementById('error-code-badge');
        if (errorBadge) {
            errorBadge.textContent = `HTTP_${statusCode}_ERROR`;
        }

        const titleErr = document.getElementById('title-error');
        if (titleErr) {
            titleErr.textContent = 'Gagal Memproses Pratinjau Perhitungan';
        }

        // Tampilkan pesan ramah pengguna tanpa membocorkan trace SQL
        textError.textContent = 'Terjadi kendala pada server saat memproses perhitungan. Silakan coba beberapa saat lagi.';
        show(alertError);
        alertError.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

        badgeStatusKalkulasi.textContent = 'Gagal';
        badgeStatusKalkulasi.className = 'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-800 border border-rose-300';
    }

    // ── Eksekusi Pemanggilan API / Kalkulasi ─────────────────────────────────
    async function executeHitungPratinjau() {
        const currentPid = selectPeriode.value;
        const currentEmpId = selectKaryawan.value;

        hideAllAlerts();

        // Ambil data komponen karyawan dari penyimpanan demo bersama (atau template bawaan jika belum ada)
        const { data: empData, isStored } = getEmployeeComponentData(currentPid, currentEmpId);

        // 1. Cek Apakah Karyawan Belum Dikonfigurasi (EMP-999 tanpa komponen atau Gaji Pokok 0 & Tanpa Komponen)
        const isUnconfigured = (currentEmpId === 'EMP-999' && !isStored) ||
                               (empData.gajiPokok === 0 && (!empData.komponen || empData.komponen.length === 0));

        if (isUnconfigured) {
            hide(stateLoading);
            hide(stateContent);
            show(stateUnconfigured);
            badgeStatusKalkulasi.textContent = 'Belum dikonfigurasi';
            badgeStatusKalkulasi.className = 'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200';
            return;
        }

        // 2. Tampilkan Kondisi Memuat (Loading State)
        hide(stateUnconfigured);
        hide(stateContent);
        show(stateLoading);

        btnHitungPratinjau.disabled = true;
        show(iconHitungSpinner);
        hide(iconHitungCalc);
        textTombolHitung.textContent = 'Memproses…';

        // Lakukan kalkulasi dalam mode demo bersama
        setTimeout(() => {
            hide(stateLoading);
            show(stateContent);

            const sourceLabel = isStored ? 'Penyimpanan Demo Bersama (Tersimpan)' : 'Mode Demo Frontend (Acuan F-5)';
            renderCalculationData(empData, sourceLabel);

            // Bersihkan flag stale setelah perhitungan dijalankan
            try {
                localStorage.removeItem(getSharedStaleKey(currentPid, currentEmpId));
            } catch (e) {}

            hasCalculated = true;
            isStale = false;
            lastCalculatedPeriodeId = currentPid;
            lastCalculatedEmployeeId = currentEmpId;

            hide(alertStale);
            stateContent.classList.remove('opacity-75');

            viewBadgeStatusHasil.textContent = 'Hasil Mutakhir';
            viewBadgeStatusHasil.className = 'px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200';

            badgeStatusKalkulasi.textContent = 'Hasil mutakhir';
            badgeStatusKalkulasi.className = 'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-300';

            textSukses.textContent = `Pratinjau perhitungan untuk ${empData.nama} (${empData.id}) berhasil diproses.`;
            show(alertSukses);

            btnHitungPratinjau.disabled = false;
            hide(iconHitungSpinner);
            show(iconHitungCalc);
            textTombolHitung.textContent = 'Hitung Pratinjau';
        }, 250);
    }

    // ── Event Listeners ─────────────────────────────────────────────────────
    if (selectPeriode) {
        selectPeriode.addEventListener('change', () => {
            updateSelectedMeta();
        });
    }

    if (selectKaryawan) {
        selectKaryawan.addEventListener('change', () => {
            updateSelectedMeta();
        });
    }

    if (btnHitungPratinjau) {
        btnHitungPratinjau.addEventListener('click', executeHitungPratinjau);
    }

    if (btnRehitungCepat) {
        btnRehitungCepat.addEventListener('click', executeHitungPratinjau);
    }

    if (btnRetryError) {
        btnRetryError.addEventListener('click', executeHitungPratinjau);
    }

    if (btnCloseAlertSukses) {
        btnCloseAlertSukses.addEventListener('click', () => hide(alertSukses));
    }

    if (btnCloseAlertError) {
        btnCloseAlertError.addEventListener('click', () => hide(alertError));
    }

    // ── Panel Pengujian Demo (Accordion & Skenario) ─────────────────────────
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

    // 1. Hitung Normal (EMP-001)
    if (btnUjiHitungNormal) {
        btnUjiHitungNormal.addEventListener('click', () => {
            selectKaryawan.value = 'EMP-001';
            updateSelectedMeta();
            executeHitungPratinjau();
        });
    }

    // 2. Belum Ada Konfigurasi (EMP-999)
    if (btnUjiTanpaKonfigurasi) {
        btnUjiTanpaKonfigurasi.addEventListener('click', () => {
            selectKaryawan.value = 'EMP-999';
            updateSelectedMeta();
            executeHitungPratinjau();
        });
    }

    // 3. Simulasi Gagal HTTP 500 (Pesan Ramah Pengguna & Detail di Log)
    if (btnUjiSimulasiGagal) {
        btnUjiSimulasiGagal.addEventListener('click', () => {
            handleServerError(500, 'Simulasi Server Error: PDOException: SQLSTATE[HY000]: database disk image is malformed in PayrollCalculationController.php on line 142.');
        });
    }

    // 4. Tandai Hasil Lama (Stale)
    if (btnUjiTandaiStale) {
        btnUjiTandaiStale.addEventListener('click', () => {
            const currentPid = selectPeriode.value;
            const currentEmpId = selectKaryawan.value;
            try {
                localStorage.setItem(getSharedStaleKey(currentPid, currentEmpId), 'true');
            } catch (e) {}
            checkStaleCondition();
        });
    }

    // ── Inisialisasi Pertama Kali ───────────────────────────────────────────
    updateSelectedMeta();

    const currentInitialPid = selectPeriode.value;
    const currentInitialEmpId = selectKaryawan.value;

    // Jika data ditandai stale di shared storage, render pratinjau dengan tanda stale (jangan auto-reset)
    if (isDataMarkedStale(currentInitialPid, currentInitialEmpId)) {
        const { data: initialEmp, isStored } = getEmployeeComponentData(currentInitialPid, currentInitialEmpId);
        show(stateContent);
        renderCalculationData(initialEmp, isStored ? 'Penyimpanan Demo Bersama (Tersimpan)' : 'Mode Demo Frontend (Acuan F-5)');
        hasCalculated = true;
        checkStaleCondition();
    } else {
        // Hitung pratinjau otomatis untuk karyawan terpilih pertama kali
        executeHitungPratinjau();
    }
}

// Jalankan otomatis saat DOM siap
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPratinjauPerhitungan);
} else {
    initPratinjauPerhitungan();
}

