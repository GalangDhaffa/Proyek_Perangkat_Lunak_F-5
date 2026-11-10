/**
 * Komponen Gaji Frontend Module - ERP PT Anaheim Nimbus Universal (Kelompok 5 F-5)
 * Mendukung FTR-K05-02 dan UI-K05-02 (Figma node-id 5-46).
 *
 * Catatan Arsitektur:
 * - Kontrak API backend untuk komponen gaji belum tersedia (mode demo).
 * - Modul ini menggunakan dataset demo terpisah dengan penanda demo yang jelas.
 * - Validasi formulir dijalankan secara ketat di client-side.
 * - Perubahan disimpan di dalam in-memory state sesi demo browser tanpa mengubah basis data backend.
 */

// Format Rupiah standar ERP F-5
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

// Dataset Karyawan & Komponen Demo (FTR-K05-02 / Sesuai Aturan F-5)
// Catatan: EMP-001 adalah referensi demo cuti F-4. Jangan mengklaim referensi F-2 sebelum pemetaan dikonfirmasi.
// Placeholder jabatan dan departemen menggunakan "Belum tersedia".
const DEMO_EMPLOYEES = [
    {
        id: 'EMP-001',
        nama: 'EMP-001 (Referensi demo cuti F-4)',
        jabatan: 'Belum tersedia',
        departemen: 'Belum tersedia',
        gajiPokok: 7000000,
        bonus: 750000,
        bpjsKaryawan: 65000,
        bpjsPerusahaan: 260000,
        komponen: [
            {
                id: 'k-1',
                nama: 'Potongan Keterlambatan',
                jenis: 'potongan',
                nominal: 200000,
                aktif: true,
            },
        ],
    },
    {
        id: 'EMP-002',
        nama: 'EMP-002',
        jabatan: 'Belum tersedia',
        departemen: 'Belum tersedia',
        gajiPokok: 6500000,
        bonus: 500000,
        bpjsKaryawan: 65000,
        bpjsPerusahaan: 260000,
        komponen: [
            {
                id: 'k-2',
                nama: 'Tunjangan Komunikasi',
                jenis: 'pendapatan',
                nominal: 250000,
                aktif: true,
            },
            {
                id: 'k-3',
                nama: 'Iuran Koperasi',
                jenis: 'potongan',
                nominal: 100000,
                aktif: true,
            },
        ],
    },
    {
        id: 'EMP-003',
        nama: 'EMP-003',
        jabatan: 'Belum tersedia',
        departemen: 'Belum tersedia',
        gajiPokok: 8000000,
        bonus: 0,
        bpjsKaryawan: 80000,
        bpjsPerusahaan: 320000,
        komponen: [
            {
                id: 'k-4',
                nama: 'Tunjangan On-Call',
                jenis: 'pendapatan',
                nominal: 600000,
                aktif: true,
            },
        ],
    },
    {
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
];

export function initKomponenGaji() {
    const root = document.getElementById('komponen-gaji-app');
    if (!root) return;

    // ── Elemen DOM ──────────────────────────────────────────────────────────
    const selectPeriode        = document.getElementById('select-periode');
    const selectKaryawan       = document.getElementById('select-karyawan');
    const btnNavKePeriode      = document.getElementById('btn-nav-ke-periode');
    const btnNavKePratinjau    = document.getElementById('btn-nav-ke-pratinjau');

    const textPeriodeRentang   = document.getElementById('text-periode-rentang');
    const badgePeriodeStatus   = document.getElementById('badge-periode-status');
    const textKaryawanDept     = document.getElementById('text-karyawan-departemen');
    const textKaryawanJabatan  = document.getElementById('text-karyawan-jabatan');

    // Alerts
    const alertSukses          = document.getElementById('alert-sukses');
    const textSukses           = document.getElementById('text-sukses');
    const btnCloseAlertSukses  = document.getElementById('btn-close-alert-sukses');

    const alertError           = document.getElementById('alert-error');
    const titleError           = document.getElementById('title-error');
    const textError            = document.getElementById('text-error');
    const listError            = document.getElementById('list-error');
    const btnCloseAlertError   = document.getElementById('btn-close-alert-error');

    // States
    const stateLoading         = document.getElementById('state-loading');
    const stateEmptyBanner     = document.getElementById('state-empty-banner');
    const btnIsiTemplate       = document.getElementById('btn-isi-template-standar');
    const formKomponenGaji     = document.getElementById('form-komponen-gaji');

    // Inputs Pokok & BPJS
    const inputGajiPokok       = document.getElementById('input-gaji-pokok');
    const inputBonus           = document.getElementById('input-bonus');
    const inputBpjsKaryawan    = document.getElementById('input-bpjs-karyawan');
    const inputBpjsPerusahaan  = document.getElementById('input-bpjs-perusahaan');

    const errorGajiPokok       = document.getElementById('error-gaji-pokok');
    const errorBonus           = document.getElementById('error-bonus');
    const errorBpjsKaryawan    = document.getElementById('error-bpjs-karyawan');
    const errorBpjsPerusahaan  = document.getElementById('error-bpjs-perusahaan');

    // Stat Cards
    const statTotalPendapatan  = document.getElementById('stat-total-pendapatan');
    const statTotalPotongan    = document.getElementById('stat-total-potongan');
    const statBpjsKaryawan     = document.getElementById('stat-bpjs-karyawan');
    const statGajiBersih       = document.getElementById('stat-gaji-bersih');

    // Tabel Komponen
    const tbodyKomponen        = document.getElementById('tbody-komponen');
    const stateTabelKosong     = document.getElementById('state-tabel-kosong');
    const btnTambahBaris       = document.getElementById('btn-tambah-baris');

    // Footer Tombol
    const btnResetForm         = document.getElementById('btn-reset-form');
    const btnSimpanKomponen    = document.getElementById('btn-simpan-komponen');
    const iconSimpanSpinner    = document.getElementById('icon-simpan-spinner');
    const textTombolSimpan     = document.getElementById('text-tombol-simpan');

    // Accordion Pengujian Demo
    const btnTogglePanelUji    = document.getElementById('btn-toggle-panel-uji');
    const panelPengujian       = document.getElementById('panel-pengujian');
    const iconToggleUji        = document.getElementById('icon-toggle-uji');
    const btnUjiNormal         = document.getElementById('btn-uji-normal');
    const btnUjiKosong         = document.getElementById('btn-uji-kosong');
    const btnUjiGagalValidasi  = document.getElementById('btn-uji-gagal-validasi');
    const btnUjiSimulasiError  = document.getElementById('btn-uji-simulasi-error');

    // ── Penyimpanan Demo Bersama (Shared Demo Storage) ────────────────────────
    // Menggunakan localStorage agar data tersimpan sinkron dengan halaman Pratinjau Perhitungan
    const DEMO_STORAGE_PREFIX = 'erpf5_demo_komponen_';
    const DEMO_STALE_PREFIX   = 'erpf5_demo_stale_';

    const stateCache = new Map();

    function getStorageKey(periodeId, employeeId) {
        return `${periodeId || 'default'}:${employeeId || 'EMP-001'}`;
    }

    function getSharedStorageKey(periodeId, employeeId) {
        return `${DEMO_STORAGE_PREFIX}${periodeId || 'default'}_${employeeId || 'EMP-001'}`;
    }

    function getSharedStaleKey(periodeId, employeeId) {
        return `${DEMO_STALE_PREFIX}${periodeId || 'default'}_${employeeId || 'EMP-001'}`;
    }

    function getEmployeeData(employeeId) {
        return DEMO_EMPLOYEES.find(e => e.id === employeeId) || DEMO_EMPLOYEES[0];
    }

    function getWorkingData(periodeId, employeeId) {
        const key = getStorageKey(periodeId, employeeId);
        if (stateCache.has(key)) {
            return stateCache.get(key);
        }

        // Cek apakah ada data tersimpan di penyimpanan demo bersama (localStorage)
        const sharedKey = getSharedStorageKey(periodeId, employeeId);
        try {
            const savedRaw = localStorage.getItem(sharedKey);
            if (savedRaw) {
                const parsed = JSON.parse(savedRaw);
                stateCache.set(key, parsed);
                return parsed;
            }
        } catch (e) {
            console.warn('[komponen-gaji] Gagal membaca shared demo storage:', e);
        }

        // Jika belum ada di storage, gunakan template dasar
        const base = getEmployeeData(employeeId);
        const cloned = JSON.parse(JSON.stringify(base));
        stateCache.set(key, cloned);
        return cloned;
    }

    function setWorkingData(periodeId, employeeId, data, persistToStorage = false) {
        const key = getStorageKey(periodeId, employeeId);
        const cloned = JSON.parse(JSON.stringify(data));
        stateCache.set(key, cloned);

        if (persistToStorage) {
            try {
                const sharedKey = getSharedStorageKey(periodeId, employeeId);
                localStorage.setItem(sharedKey, JSON.stringify(cloned));
                // Tandai bahwa pratinjau perhitungan untuk periode & employee ini perlu dihitung ulang
                localStorage.setItem(getSharedStaleKey(periodeId, employeeId), 'true');
            } catch (e) {
                console.warn('[komponen-gaji] Gagal menulis ke shared demo storage:', e);
            }
        }
    }

    // ── Baca Periode Awal dari Database / Attributes ────────────────────────
    let initialPeriods = [];
    try {
        const rawPeriods = root.dataset.initialPeriods;
        if (rawPeriods) {
            initialPeriods = JSON.parse(rawPeriods);
        }
    } catch {
        initialPeriods = [];
    }

    const selectedPeriodeIdFromAttr = root.dataset.selectedPeriodeId;
    if (selectedPeriodeIdFromAttr && selectPeriode) {
        // Cari option yang cocok (case-insensitive)
        const match = Array.from(selectPeriode.options).find(
            opt => opt.value.toLowerCase() === selectedPeriodeIdFromAttr.toLowerCase()
        );
        if (match) {
            selectPeriode.value = match.value;
        }
    }

    const selectedEmployeeIdFromAttr = root.dataset.selectedEmployeeId;
    if (selectedEmployeeIdFromAttr && selectKaryawan) {
        const empMatch = Array.from(selectKaryawan.options).find(
            opt => opt.value.toLowerCase() === selectedEmployeeIdFromAttr.toLowerCase()
        );
        if (empMatch) {
            selectKaryawan.value = empMatch.value;
        }
    }

    // Perbarui link navigasi ke periode dan pratinjau serta jaga query string URL
    function updateNavigationLinks() {
        const currentPid = selectPeriode ? selectPeriode.value : '';
        const currentEmpId = selectKaryawan ? selectKaryawan.value : '';

        const params = new URLSearchParams();
        if (currentPid) params.set('periodeId', currentPid);
        if (currentEmpId) params.set('employeeId', currentEmpId);

        const paramString = params.toString() ? '?' + params.toString() : '';

        if (btnNavKePeriode) {
            btnNavKePeriode.href = currentPid ? `/periode-payroll?periodeId=${encodeURIComponent(currentPid)}` : `/periode-payroll`;
        }

        if (btnNavKePratinjau) {
            btnNavKePratinjau.href = `/pratinjau-perhitungan${paramString}`;
        }

        // Sinkronkan parameter query di address bar browser tanpa reload
        if (window.history && window.history.replaceState && paramString) {
            const newUrl = `${window.location.pathname}${paramString}`;
            window.history.replaceState(null, '', newUrl);
        }
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

    function hideAlerts() {
        hide(alertSukses);
        hide(alertError);
        if (listError) {
            listError.innerHTML = '';
            hide(listError);
        }
    }

    function clearValidationErrors() {
        [errorGajiPokok, errorBonus, errorBpjsKaryawan, errorBpjsPerusahaan].forEach(errEl => {
            if (errEl) {
                errEl.textContent = '';
                hide(errEl);
            }
        });

        [inputGajiPokok, inputBonus, inputBpjsKaryawan, inputBpjsPerusahaan].forEach(inputEl => {
            if (inputEl) {
                inputEl.classList.remove('border-rose-400', 'focus:ring-rose-500', 'bg-rose-50/20');
            }
        });

        // Hapus penanda error pada baris tabel
        const tableInputs = tbodyKomponen.querySelectorAll('input');
        tableInputs.forEach(inp => {
            inp.classList.remove('border-rose-400', 'focus:ring-rose-500', 'bg-rose-50/20');
        });
    }

    // ── Perbarui Metadata Periode ───────────────────────────────────────────
    function updatePeriodeMeta() {
        if (!selectPeriode) return;
        const selectedVal = selectPeriode.value;
        const found = initialPeriods.find(p => (p.uuid || p.id) === selectedVal);

        if (found) {
            const mulai = found.tanggal_mulai || found.tanggalMulai || '—';
            const akhir = found.tanggal_akhir || found.tanggalAkhir || '—';
            textPeriodeRentang.textContent = `${mulai} s.d. ${akhir}`;
            badgePeriodeStatus.textContent = found.status || 'Draf';
        } else {
            textPeriodeRentang.textContent = '01 Okt 2026 s.d. 31 Okt 2026';
            badgePeriodeStatus.textContent = 'Draf';
        }

        updateNavigationLinks();
    }

    // ── Render Form & Tabel Komponen ────────────────────────────────────────
    function renderForm() {
        hideAlerts();
        clearValidationErrors();

        const currentPeriodeId = selectPeriode.value;
        const currentEmpId = selectKaryawan.value;
        const data = getWorkingData(currentPeriodeId, currentEmpId);

        // Update Info Karyawan
        textKaryawanDept.textContent = data.departemen || '—';
        textKaryawanJabatan.textContent = data.jabatan || '—';

        // Set Input Pokok & Agregat
        inputGajiPokok.value = data.gajiPokok || 0;
        inputBonus.value = data.bonus || 0;
        inputBpjsKaryawan.value = data.bpjsKaryawan || 0;
        inputBpjsPerusahaan.value = data.bpjsPerusahaan || 0;

        // Render Baris Komponen
        renderKomponenRows(data.komponen || []);

        // Cek Kondisi Kosong (EMP-999 atau semua 0)
        const isCompletelyEmpty = (data.gajiPokok === 0 && (!data.komponen || data.komponen.length === 0));
        if (isCompletelyEmpty) {
            show(stateEmptyBanner);
        } else {
            hide(stateEmptyBanner);
        }

        // Hitung Total Real-time
        recalculateTotals();
        updateNavigationLinks();
    }

    function renderKomponenRows(komponenList) {
        tbodyKomponen.innerHTML = '';

        if (!komponenList || komponenList.length === 0) {
            show(stateTabelKosong);
            return;
        }

        hide(stateTabelKosong);

        komponenList.forEach((item, index) => {
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-slate-50/50 transition-colors group';
            tr.dataset.index = index;
            tr.dataset.id = item.id || `row-${index}`;

            const isPendapatan = item.jenis === 'pendapatan';
            const isAktif = item.aktif !== false;

            tr.innerHTML = `
                <td class="px-4 py-3">
                    <input
                        type="text"
                        class="input-row-nama w-full bg-white border border-slate-300 rounded-lg px-3 py-1.5 text-sm text-slate-800 placeholder-slate-400 focus:outline-hidden focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors shadow-2xs"
                        placeholder="Contoh: Tunjangan Transport / Potongan Absen"
                        value="${escapeHtml(item.nama || '')}"
                        aria-label="Nama komponen"
                    >
                </td>
                <td class="px-4 py-3">
                    <div class="relative">
                        <select
                            class="select-row-jenis w-full bg-white border border-slate-300 rounded-lg px-2.5 py-1.5 text-xs font-semibold focus:outline-hidden focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors shadow-2xs ${
                                isPendapatan ? 'text-emerald-700 bg-emerald-50/30' : 'text-rose-700 bg-rose-50/30'
                            }"
                            aria-label="Jenis komponen"
                        >
                            <option value="pendapatan" ${isPendapatan ? 'selected' : ''}>+ Pendapatan</option>
                            <option value="potongan" ${!isPendapatan ? 'selected' : ''}>&minus; Potongan</option>
                        </select>
                    </div>
                </td>
                <td class="px-4 py-3">
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-xs font-semibold text-slate-400">
                            Rp
                        </span>
                        <input
                            type="number"
                            min="0"
                            step="1000"
                            class="input-row-nominal w-full bg-white border border-slate-300 rounded-lg pl-8 pr-2.5 py-1.5 text-sm text-slate-800 font-mono focus:outline-hidden focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors shadow-2xs"
                            placeholder="0"
                            value="${item.nominal !== undefined ? item.nominal : 0}"
                            aria-label="Nominal komponen"
                        >
                    </div>
                </td>
                <td class="px-4 py-3 text-center">
                    <label class="inline-flex items-center gap-1.5 cursor-pointer select-none">
                        <input
                            type="checkbox"
                            class="checkbox-row-aktif w-4 h-4 text-blue-600 rounded-sm border-slate-300 focus:ring-blue-500 cursor-pointer"
                            ${isAktif ? 'checked' : ''}
                        >
                        <span class="badge-status-aktif text-[11px] font-semibold px-2 py-0.5 rounded ${
                            isAktif ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500'
                        }">
                            ${isAktif ? 'Aktif' : 'Nonaktif'}
                        </span>
                    </label>
                </td>
                <td class="px-4 py-3 text-center">
                    <button
                        type="button"
                        class="btn-row-hapus p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors"
                        title="Hapus baris ini"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </td>
            `;

            tbodyKomponen.appendChild(tr);
        });

        attachRowEvents();
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

    // ── Row Event Listeners ─────────────────────────────────────────────────
    function attachRowEvents() {
        const rows = tbodyKomponen.querySelectorAll('tr');
        rows.forEach(row => {
            const inputNama = row.querySelector('.input-row-nama');
            const selectJenis = row.querySelector('.select-row-jenis');
            const inputNominal = row.querySelector('.input-row-nominal');
            const checkboxAktif = row.querySelector('.checkbox-row-aktif');
            const badgeAktif = row.querySelector('.badge-status-aktif');
            const btnHapus = row.querySelector('.btn-row-hapus');

            // Ubah Jenis (Warna dropdown & recalculate)
            if (selectJenis) {
                selectJenis.addEventListener('change', () => {
                    const isPendapatan = selectJenis.value === 'pendapatan';
                    selectJenis.className = `select-row-jenis w-full bg-white border border-slate-300 rounded-lg px-2.5 py-1.5 text-xs font-semibold focus:outline-hidden focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors shadow-2xs ${
                        isPendapatan ? 'text-emerald-700 bg-emerald-50/30' : 'text-rose-700 bg-rose-50/30'
                    }`;
                    syncWorkingDataFromDom();
                    recalculateTotals();
                });
            }

            // Input Nominal / Nama
            if (inputNominal) {
                inputNominal.addEventListener('input', () => {
                    syncWorkingDataFromDom();
                    recalculateTotals();
                });
            }

            if (inputNama) {
                inputNama.addEventListener('input', () => {
                    if (inputNama.value.trim().length > 0) {
                        inputNama.classList.remove('border-rose-400', 'bg-rose-50/20');
                    }
                    syncWorkingDataFromDom();
                });
            }

            // Toggle Aktif/Nonaktif
            if (checkboxAktif && badgeAktif) {
                checkboxAktif.addEventListener('change', () => {
                    const isChecked = checkboxAktif.checked;
                    badgeAktif.textContent = isChecked ? 'Aktif' : 'Nonaktif';
                    badgeAktif.className = `badge-status-aktif text-[11px] font-semibold px-2 py-0.5 rounded ${
                        isChecked ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500'
                    }`;
                    syncWorkingDataFromDom();
                    recalculateTotals();
                });
            }

            // Hapus Baris
            if (btnHapus) {
                btnHapus.addEventListener('click', () => {
                    row.remove();
                    if (tbodyKomponen.children.length === 0) {
                        show(stateTabelKosong);
                    }
                    syncWorkingDataFromDom();
                    recalculateTotals();
                });
            }
        });
    }

    // ── Sinkronisasi DOM ke In-Memory Data ───────────────────────────────────
    function syncWorkingDataFromDom() {
        const currentPeriodeId = selectPeriode.value;
        const currentEmpId = selectKaryawan.value;
        const currentData = getWorkingData(currentPeriodeId, currentEmpId);

        currentData.gajiPokok = parseFloat(inputGajiPokok.value) || 0;
        currentData.bonus = parseFloat(inputBonus.value) || 0;
        currentData.bpjsKaryawan = parseFloat(inputBpjsKaryawan.value) || 0;
        currentData.bpjsPerusahaan = parseFloat(inputBpjsPerusahaan.value) || 0;

        const newKomponen = [];
        const rows = tbodyKomponen.querySelectorAll('tr');
        rows.forEach((row, idx) => {
            const inputNama = row.querySelector('.input-row-nama');
            const selectJenis = row.querySelector('.select-row-jenis');
            const inputNominal = row.querySelector('.input-row-nominal');
            const checkboxAktif = row.querySelector('.checkbox-row-aktif');

            newKomponen.push({
                id: row.dataset.id || `k-new-${idx}`,
                nama: inputNama ? inputNama.value.trim() : '',
                jenis: selectJenis ? selectJenis.value : 'pendapatan',
                nominal: inputNominal ? (parseFloat(inputNominal.value) || 0) : 0,
                aktif: checkboxAktif ? checkboxAktif.checked : true,
            });
        });

        currentData.komponen = newKomponen;
        setWorkingData(currentPeriodeId, currentEmpId, currentData);
    }

    // ── Real-time Total Calculation ─────────────────────────────────────────
    // Aturan F-5:
    // Total Pendapatan = Gaji Pokok + Bonus + Komponen Pendapatan Aktif
    // Total Potongan Komponen = Komponen Potongan Aktif (di luar BPJS Karyawan)
    // BPJS Karyawan dicatat terpisah
    // Estimasi Gaji Bersih = Total Pendapatan - (Total Potongan Komponen + BPJS Karyawan)
    function recalculateTotals() {
        const gajiPokok = parseFloat(inputGajiPokok.value) || 0;
        const bonus = parseFloat(inputBonus.value) || 0;
        const bpjsKaryawan = parseFloat(inputBpjsKaryawan.value) || 0;

        let totalKomponenPendapatan = 0;
        let totalKomponenPotongan = 0;

        const rows = tbodyKomponen.querySelectorAll('tr');
        rows.forEach(row => {
            const selectJenis = row.querySelector('.select-row-jenis');
            const inputNominal = row.querySelector('.input-row-nominal');
            const checkboxAktif = row.querySelector('.checkbox-row-aktif');

            const isAktif = checkboxAktif ? checkboxAktif.checked : true;
            const nominal = inputNominal ? (parseFloat(inputNominal.value) || 0) : 0;
            const jenis = selectJenis ? selectJenis.value : 'pendapatan';

            if (isAktif) {
                if (jenis === 'pendapatan') {
                    totalKomponenPendapatan += nominal;
                } else {
                    totalKomponenPotongan += nominal;
                }
            }
        });

        const totalPendapatan = gajiPokok + bonus + totalKomponenPendapatan;
        const gajiBersih = totalPendapatan - (totalKomponenPotongan + bpjsKaryawan);

        statTotalPendapatan.textContent = formatRupiah(totalPendapatan);
        statTotalPotongan.textContent = formatRupiah(totalKomponenPotongan);
        statBpjsKaryawan.textContent = formatRupiah(bpjsKaryawan);
        statGajiBersih.textContent = formatRupiah(gajiBersih);
    }

    // ── Tambah Baris Komponen Baru ──────────────────────────────────────────
    function addKomponenRow() {
        hide(stateTabelKosong);

        const newIndex = tbodyKomponen.children.length;
        const tr = document.createElement('tr');
        tr.className = 'hover:bg-slate-50/50 transition-colors group animate-fadeIn';
        tr.dataset.index = newIndex;
        tr.dataset.id = `k-new-${Date.now()}`;

        tr.innerHTML = `
            <td class="px-4 py-3">
                <input
                    type="text"
                    class="input-row-nama w-full bg-white border border-slate-300 rounded-lg px-3 py-1.5 text-sm text-slate-800 placeholder-slate-400 focus:outline-hidden focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors shadow-2xs"
                    placeholder="Contoh: Tunjangan Transport / Potongan Absen"
                    value=""
                    aria-label="Nama komponen"
                >
            </td>
            <td class="px-4 py-3">
                <div class="relative">
                    <select
                        class="select-row-jenis w-full bg-white border border-slate-300 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-emerald-700 bg-emerald-50/30 focus:outline-hidden focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors shadow-2xs"
                        aria-label="Jenis komponen"
                    >
                        <option value="pendapatan" selected>+ Pendapatan</option>
                        <option value="potongan">&minus; Potongan</option>
                    </select>
                </div>
            </td>
            <td class="px-4 py-3">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-xs font-semibold text-slate-400">
                        Rp
                    </span>
                    <input
                        type="number"
                        min="0"
                        step="1000"
                        class="input-row-nominal w-full bg-white border border-slate-300 rounded-lg pl-8 pr-2.5 py-1.5 text-sm text-slate-800 font-mono focus:outline-hidden focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors shadow-2xs"
                        placeholder="0"
                        value="0"
                        aria-label="Nominal komponen"
                    >
                </div>
            </td>
            <td class="px-4 py-3 text-center">
                <label class="inline-flex items-center gap-1.5 cursor-pointer select-none">
                    <input
                        type="checkbox"
                        class="checkbox-row-aktif w-4 h-4 text-blue-600 rounded-sm border-slate-300 focus:ring-blue-500 cursor-pointer"
                        checked
                    >
                    <span class="badge-status-aktif text-[11px] font-semibold px-2 py-0.5 rounded bg-emerald-50 text-emerald-700">
                        Aktif
                    </span>
                </label>
            </td>
            <td class="px-4 py-3 text-center">
                <button
                    type="button"
                    class="btn-row-hapus p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors"
                    title="Hapus baris ini"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>
            </td>
        `;

        tbodyKomponen.appendChild(tr);
        attachRowEvents();

        const inputBaru = tr.querySelector('.input-row-nama');
        if (inputBaru) {
            inputBaru.focus();
        }

        syncWorkingDataFromDom();
        recalculateTotals();
    }

    // ── Validasi Formulir ───────────────────────────────────────────────────
    function validateForm() {
        clearValidationErrors();
        const errors = [];

        // Validasi Gaji Pokok
        const gajiPokokVal = inputGajiPokok.value.trim();
        if (gajiPokokVal === '' || isNaN(gajiPokokVal)) {
            errors.push('Gaji pokok wajib diisi dengan nominal angka.');
            inputGajiPokok.classList.add('border-rose-400', 'bg-rose-50/20');
            errorGajiPokok.textContent = 'Gaji pokok wajib diisi.';
            show(errorGajiPokok);
        } else if (parseFloat(gajiPokokVal) < 0) {
            errors.push('Gaji pokok tidak boleh bernilai negatif.');
            inputGajiPokok.classList.add('border-rose-400', 'bg-rose-50/20');
            errorGajiPokok.textContent = 'Nominal tidak boleh negatif.';
            show(errorGajiPokok);
        }

        // Validasi Bonus
        const bonusVal = inputBonus.value.trim();
        if (bonusVal !== '' && parseFloat(bonusVal) < 0) {
            errors.push('Nominal bonus tidak boleh negatif.');
            inputBonus.classList.add('border-rose-400', 'bg-rose-50/20');
            errorBonus.textContent = 'Nominal tidak boleh negatif.';
            show(errorBonus);
        }

        // Validasi BPJS Karyawan
        const bpjsKaryawanVal = inputBpjsKaryawan.value.trim();
        if (bpjsKaryawanVal !== '' && parseFloat(bpjsKaryawanVal) < 0) {
            errors.push('Nominal BPJS karyawan tidak boleh negatif.');
            inputBpjsKaryawan.classList.add('border-rose-400', 'bg-rose-50/20');
            errorBpjsKaryawan.textContent = 'Nominal tidak boleh negatif.';
            show(errorBpjsKaryawan);
        }

        // Validasi BPJS Perusahaan
        const bpjsPerusahaanVal = inputBpjsPerusahaan.value.trim();
        if (bpjsPerusahaanVal !== '' && parseFloat(bpjsPerusahaanVal) < 0) {
            errors.push('Nominal BPJS perusahaan tidak boleh negatif.');
            inputBpjsPerusahaan.classList.add('border-rose-400', 'bg-rose-50/20');
            errorBpjsPerusahaan.textContent = 'Nominal tidak boleh negatif.';
            show(errorBpjsPerusahaan);
        }

        // Validasi Baris Komponen Tambahan
        const rows = tbodyKomponen.querySelectorAll('tr');
        rows.forEach((row, i) => {
            const inputNama = row.querySelector('.input-row-nama');
            const inputNominal = row.querySelector('.input-row-nominal');

            if (inputNama) {
                const nama = inputNama.value.trim();
                if (nama.length === 0) {
                    errors.push(`Baris ${i + 1}: Nama komponen tidak boleh kosong.`);
                    inputNama.classList.add('border-rose-400', 'bg-rose-50/20');
                }
            }

            if (inputNominal) {
                const nominal = parseFloat(inputNominal.value);
                if (isNaN(nominal) || nominal < 0) {
                    errors.push(`Baris ${i + 1}: Nominal komponen harus berupa angka positif (&ge; 0).`);
                    inputNominal.classList.add('border-rose-400', 'bg-rose-50/20');
                }
            }
        });

        return errors;
    }

    // ── Submit Formulir (Simpan Komponen) ────────────────────────────────────
    formKomponenGaji.addEventListener('submit', (e) => {
        e.preventDefault();
        hideAlerts();

        const errors = validateForm();
        if (errors.length > 0) {
            titleError.textContent = 'Terjadi kesalahan validasi formulir';
            textError.textContent = `Ditemukan ${errors.length} kesalahan pengisian yang perlu diperbaiki:`;
            listError.innerHTML = '';
            errors.forEach(msg => {
                const li = document.createElement('li');
                li.innerHTML = msg;
                listError.appendChild(li);
            });
            show(listError);
            show(alertError);
            alertError.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            return;
        }

        // Mulai proses simpan lokal (Simulasi delay UI 350ms)
        btnSimpanKomponen.disabled = true;
        show(iconSimpanSpinner);
        textTombolSimpan.textContent = 'Menyimpan…';

        setTimeout(() => {
            syncWorkingDataFromDom();

            const currentPeriodeId = selectPeriode.value;
            const currentEmpId = selectKaryawan.value;
            const currentEmp = getWorkingData(currentPeriodeId, currentEmpId);
            // Simpan ke shared demo storage dan tandai stale untuk pratinjau
            setWorkingData(currentPeriodeId, currentEmpId, currentEmp, true);

            const namaPeriode = selectPeriode.options[selectPeriode.selectedIndex]?.textContent || 'Periode Terpilih';

            btnSimpanKomponen.disabled = false;
            hide(iconSimpanSpinner);
            textTombolSimpan.textContent = 'Simpan komponen';

            textSukses.textContent = `Komponen gaji untuk ${currentEmp.nama} (${currentEmp.id}) pada ${namaPeriode} berhasil divalidasi dan disimpan ke penyimpanan demo bersama. Pratinjau perhitungan ditandai perlu dihitung ulang.`;
            show(alertSukses);
            alertSukses.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }, 350);
    });

    // ── Input Listeners Gaji Pokok & Agregat ──────────────────────────────────
    [inputGajiPokok, inputBonus, inputBpjsKaryawan, inputBpjsPerusahaan].forEach(inputEl => {
        if (inputEl) {
            inputEl.addEventListener('input', () => {
                syncWorkingDataFromDom();
                recalculateTotals();
            });
        }
    });

    // ── Tambah Baris Baru ───────────────────────────────────────────────────
    if (btnTambahBaris) {
        btnTambahBaris.addEventListener('click', addKomponenRow);
    }

    // ── Muat Template Standar (untuk Data Kosong) ────────────────────────────
    if (btnIsiTemplate) {
        btnIsiTemplate.addEventListener('click', () => {
            inputGajiPokok.value = 5000000;
            inputBonus.value = 0;
            inputBpjsKaryawan.value = 50000;
            inputBpjsPerusahaan.value = 200000;

            const contohKomponen = [
                { id: `k-${Date.now()}-1`, nama: 'Tunjangan Operasional', jenis: 'pendapatan', nominal: 300000, aktif: true },
                { id: `k-${Date.now()}-2`, nama: 'Iuran Kas Karyawan', jenis: 'potongan', nominal: 50000, aktif: true },
            ];

            renderKomponenRows(contohKomponen);
            hide(stateEmptyBanner);
            syncWorkingDataFromDom();
            recalculateTotals();
        });
    }

    // ── Reset Formulir ──────────────────────────────────────────────────────
    if (btnResetForm) {
        btnResetForm.addEventListener('click', () => {
            const currentPeriodeId = selectPeriode.value;
            const currentEmpId = selectKaryawan.value;
            try {
                localStorage.removeItem(getSharedStorageKey(currentPeriodeId, currentEmpId));
                localStorage.setItem(getSharedStaleKey(currentPeriodeId, currentEmpId), 'true');
            } catch (e) {
                console.warn('[komponen-gaji] Gagal mereset shared storage:', e);
            }
            // Ambil ulang dari template dasar
            const base = getEmployeeData(currentEmpId);
            setWorkingData(currentPeriodeId, currentEmpId, base, false);
            renderForm();
        });
    }

    // ── Ganti Karyawan atau Periode (Loading State) ──────────────────────────
    function switchContextWithLoading() {
        hideAlerts();
        updateNavigationLinks();
        show(stateLoading);
        hide(formKomponenGaji);
        hide(stateEmptyBanner);

        setTimeout(() => {
            hide(stateLoading);
            show(formKomponenGaji);
            renderForm();
        }, 200);
    }

    if (selectKaryawan) {
        selectKaryawan.addEventListener('change', switchContextWithLoading);
    }

    if (selectPeriode) {
        selectPeriode.addEventListener('change', () => {
            updatePeriodeMeta();
            switchContextWithLoading();
        });
    }

    // Close buttons for alerts
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

    // 1. Skenario Normal (EMP-001)
    if (btnUjiNormal) {
        btnUjiNormal.addEventListener('click', () => {
            selectKaryawan.value = 'EMP-001';
            switchContextWithLoading();
        });
    }

    // 2. Skenario Kosong (EMP-999)
    if (btnUjiKosong) {
        btnUjiKosong.addEventListener('click', () => {
            selectKaryawan.value = 'EMP-999';
            switchContextWithLoading();
        });
    }

    // 3. Simulasi Validasi Gagal
    if (btnUjiGagalValidasi) {
        btnUjiGagalValidasi.addEventListener('click', () => {
            hideAlerts();
            inputGajiPokok.value = '';
            inputBonus.value = -50000;
            addKomponenRow(); // baris baru tanpa nama
            validateForm();

            titleError.textContent = 'Simulasi Validasi Gagal Berhasil Ditampilkan';
            textError.textContent = 'Contoh pesan validasi ketika formulir tidak lengkap atau bernilai negatif:';
            listError.innerHTML = `
                <li>Gaji pokok wajib diisi dengan nominal angka.</li>
                <li>Nominal bonus tidak boleh negatif.</li>
                <li>Baris komponen baru belum memiliki nama komponen.</li>
            `;
            show(listError);
            show(alertError);
            alertError.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        });
    }

    // 4. Simulasi Error Backend 503
    if (btnUjiSimulasiError) {
        btnUjiSimulasiError.addEventListener('click', () => {
            hideAlerts();
            titleError.textContent = '503 SERVICE_UNAVAILABLE';
            textError.textContent = 'Simulasi: Layanan API backend payroll tidak dapat dihubungi (mode demo — endpoint belum tersedia).';
            listError.innerHTML = `
                <li>Endpoint POST /api/payroll/v1/komponen-gaji belum tersedia pada kontrak aktif.</li>
                <li>Silakan tunggu pembaruan backend. Referensi kontrak: FTR-K05-02.</li>
            `;
            show(listError);
            show(alertError);
            alertError.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        });
    }

    // ── Inisialisasi Pertama Kali ───────────────────────────────────────────
    updatePeriodeMeta();
    renderForm();
}

// Jalankan otomatis saat DOM siap
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initKomponenGaji);
} else {
    initKomponenGaji();
}

