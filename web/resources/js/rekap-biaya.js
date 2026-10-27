/**
 * Rekap Biaya Frontend Module - ERP PT Anaheim Nimbus Universal (Kelompok 5 F-5)
 * Kontrak OpenAPI v0.2, endpoint GET /api/payroll/v1/rekap-biaya.
 * Backend menggunakan fixture demo — bukan integrasi nyata F-1/F-2/F-3/F-4.
 */

/** Batas waktu request (ms). Mencegah loading terkunci tanpa batas. */
const REQUEST_TIMEOUT_MS = 10000;

/**
 * Format angka ke rupiah. null → 'Belum ditetapkan'.
 */
export function formatRupiah(amount) {
    if (amount === null || amount === undefined) {
        return 'Belum ditetapkan';
    }
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    }).format(amount).replace(/\s+/g, '');
}

/**
 * Format tanggal ke format Indonesia singkat.
 */
export function formatTanggal(dateStr) {
    if (!dateStr) return '—';
    try {
        return new Date(dateStr).toLocaleDateString('id-ID', {
            day: '2-digit', month: 'short', year: 'numeric',
        });
    } catch {
        return dateStr;
    }
}

/**
 * Inisialisasi halaman Rekap Biaya.
 */
export function initRekapBiaya() {
    // ── Elemen utama ──────────────────────────────────────────────────────────
    const selectPeriode       = document.getElementById('select-periode');
    const selectAuthRole      = document.getElementById('select-auth-role');
    const selectSkenarioGalat = document.getElementById('select-skenario-galat');
    const inputCustomContainer= document.getElementById('input-custom-container');
    const inputCustomUuid     = document.getElementById('input-custom-uuid');
    const btnTampilkan        = document.getElementById('btn-tampilkan-rekap');

    const stateLoading        = document.getElementById('state-loading');
    const stateError          = document.getElementById('state-error');
    const stateContent        = document.getElementById('state-content');
    const stateEmptyNotice    = document.getElementById('state-empty-notice');

    const btnRetry            = document.getElementById('btn-retry');

    // ── Panel pengujian (accordion) ───────────────────────────────────────────
    const btnTogglePanelUji   = document.getElementById('btn-toggle-panel-uji');
    const panelPengujian      = document.getElementById('panel-pengujian');
    const iconToggleUji       = document.getElementById('icon-toggle-uji');

    if (!btnTampilkan || !selectPeriode) return;

    // Toggle panel pengujian
    if (btnTogglePanelUji && panelPengujian) {
        btnTogglePanelUji.addEventListener('click', () => {
            const isOpen = !panelPengujian.classList.contains('hidden');
            panelPengujian.classList.toggle('hidden', isOpen);
            btnTogglePanelUji.setAttribute('aria-expanded', String(!isOpen));
            if (iconToggleUji) {
                iconToggleUji.style.transform = isOpen ? '' : 'rotate(90deg)';
            }
        });
    }

    // Toggle input UUID manual dari select skenario galat
    if (selectSkenarioGalat) {
        selectSkenarioGalat.addEventListener('change', () => {
            const show = selectSkenarioGalat.value === 'custom';
            inputCustomContainer.classList.toggle('hidden', !show);
            if (show && inputCustomUuid) inputCustomUuid.focus();
        });
    }

    // Tombol "Coba lagi" — jalankan ulang request terakhir
    if (btnRetry) {
        btnRetry.addEventListener('click', loadRekap);
    }

    // ── Logika utama ─────────────────────────────────────────────────────────

    /** Tentukan periodeId yang akan dikirim ke backend. */
    function resolvePeriodeId() {
        // Jika panel uji terbuka dan ada skenario galat dipilih, pakai itu
        if (selectSkenarioGalat && selectSkenarioGalat.value !== '') {
            if (selectSkenarioGalat.value === 'custom') {
                return inputCustomUuid ? inputCustomUuid.value.trim() : '';
            }
            return selectSkenarioGalat.value;
        }
        return selectPeriode.value;
    }

    /** Petakan pilihan peran ke token mock yang dikenali backend demo. */
    function resolveToken() {
        const role = selectAuthRole ? selectAuthRole.value : 'manajemen';
        const MAP = {
            manajemen:        'mock-manajemen',
            petugas_keuangan: 'mock-keuangan',
            layanan_e2:       'mock-e2',
            unauthorized:     'invalid-token-demo',
            // mock-karyawan dikenali backend sebagai peran yang tidak diizinkan → 403
            forbidden:        'mock-karyawan',
        };
        return MAP[role] ?? 'mock-manajemen';
    }

    async function loadRekap() {
        const periodeId = resolvePeriodeId();
        const token     = resolveToken();

        // Sembunyikan semua state sebelumnya, tampilkan loading
        stateLoading.classList.remove('hidden');
        stateError.classList.add('hidden');
        stateContent.classList.add('hidden');
        btnTampilkan.disabled = true;
        if (btnRetry) btnRetry.classList.add('hidden');

        try {
            const controller = new AbortController();
            const timeoutId  = setTimeout(() => controller.abort(), REQUEST_TIMEOUT_MS);

            let response;
            try {
                response = await fetch(
                    `/api/payroll/v1/rekap-biaya?periodeId=${encodeURIComponent(periodeId)}`,
                    {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'Authorization': `Bearer ${token}`,
                        },
                        signal: controller.signal,
                    }
                );
            } finally {
                clearTimeout(timeoutId);
            }

            const contentType = response.headers.get('content-type') || '';
            if (!contentType.includes('application/json')) {
                // Server merespons HTML bukan JSON — kemungkinan server belum siap
                showError({
                    code: 'SERVER_ERROR',
                    message: 'Server tidak merespons dengan benar. Pastikan aplikasi sudah berjalan.',
                    details: [],
                }, response.status || 0, true);
                return;
            }

            const json = await response.json();

            if (response.ok) {
                renderSuccess(json);
            } else {
                // Error resmi 400 / 401 / 403 / 404 dari backend
                showError(json, response.status, false);
            }

        } catch (err) {
            const isTimeout = err.name === 'AbortError';
            showError({
                code: 'CONNECTION_ERROR',
                message: isTimeout
                    ? 'Permintaan memakan waktu terlalu lama. Periksa koneksi dan coba lagi.'
                    : 'Tidak dapat terhubung ke server. Periksa koneksi dan coba lagi.',
                details: [],
            }, 0, true);
        } finally {
            stateLoading.classList.add('hidden');
            btnTampilkan.disabled = false;
        }
    }

    // ── Render sukses ─────────────────────────────────────────────────────────

    function renderSuccess(data) {
        stateContent.classList.remove('hidden');
        stateError.classList.add('hidden');

        // Header periode
        const elNama = document.getElementById('view-nama-periode');
        if (elNama) elNama.textContent = data.namaPeriode || '—';

        const elRentang = document.getElementById('view-tanggal-rentang');
        if (elRentang) elRentang.textContent =
            `${formatTanggal(data.tanggalMulai)} s.d. ${formatTanggal(data.tanggalAkhir)}`;

        const elDiperbarui = document.getElementById('view-diperbarui-pada');
        if (elDiperbarui) elDiperbarui.textContent = data.diperbaruiPada
            ? data.diperbaruiPada.replace('T', ' ').substring(0, 19) + ' WIB'
            : '—';

        // Badge status periode
        const badgeStatus = document.getElementById('view-status-periode');
        if (badgeStatus) {
            badgeStatus.textContent = data.statusPeriode || '—';
            const COLOR = {
                'Disetujui':           'bg-emerald-50 text-emerald-700 border-emerald-200',
                'Dihitung':            'bg-blue-50 text-blue-700 border-blue-200',
                'Menunggu Persetujuan':'bg-amber-50 text-amber-700 border-amber-200',
            };
            const cls = COLOR[data.statusPeriode] ?? 'bg-slate-100 text-slate-700 border-slate-200';
            badgeStatus.className = `px-2.5 py-0.5 rounded-full text-xs font-semibold border ${cls}`;
        }

        // Banner periode kosong
        if (stateEmptyNotice) {
            stateEmptyNotice.classList.toggle('hidden', !!data.adaData);
        }

        // Stat cards
        setText('stat-pendapatan', formatRupiah(data.totalPendapatan));
        setText('stat-pendapatan-subtext', `Termasuk bonus ${formatRupiah(data.totalBonus)}`);
        setText('stat-potongan', formatRupiah(data.totalPotongan));
        setText('stat-potongan-subtext', `BPJS karyawan ${formatRupiah(data.totalBpjsKaryawan)} dicatat terpisah`);
        setText('stat-gajibersih', formatRupiah(data.totalGajiBersih));

        // Ringkasan karyawan & termin di header tabel
        const elRingkasan = document.getElementById('view-ringkasan-karyawan-termin');
        if (elRingkasan) {
            const parts = [];
            if (data.jumlahKaryawan > 0) parts.push(`${data.jumlahKaryawan} karyawan`);
            if (data.jumlahTermin > 0)   parts.push(`${data.jumlahTermin} termin`);
            elRingkasan.textContent = parts.length ? parts.join(' · ') : '';
        }

        // Baris tabel
        setText('row-pendapatan', formatRupiah(data.totalPendapatan));
        setText('row-bonus', formatRupiah(data.totalBonus));
        setText('row-potongan', formatRupiah(data.totalPotongan));
        setText('row-bpjs-karyawan', formatRupiah(data.totalBpjsKaryawan));
        setText('row-gaji-bersih', formatRupiah(data.totalGajiBersih));
        setText('row-bpjs-perusahaan', formatRupiah(data.totalBpjsPerusahaan));
        setText('row-termin', formatRupiah(data.totalTermin));

        const elTerminKet = document.getElementById('row-termin-keterangan');
        if (elTerminKet) {
            elTerminKet.textContent = data.jumlahTermin > 0
                ? `${data.jumlahTermin} termin pada periode ini`
                : 'Tidak ada pembayaran termin';
        }

        // Total biaya — selalu null pada kontrak v0.2
        const elTotalBiaya = document.getElementById('row-total-biaya');
        if (elTotalBiaya) {
            if (data.totalBiaya === null) {
                elTotalBiaya.textContent = 'Belum ditetapkan';
                elTotalBiaya.className = 'py-4 text-right pr-6 italic text-slate-400 font-medium';
            } else {
                elTotalBiaya.textContent = formatRupiah(data.totalBiaya);
                elTotalBiaya.className = 'py-4 text-right pr-6 font-bold text-slate-900';
            }
        }
    }

    // ── Render error ──────────────────────────────────────────────────────────

    /**
     * Tampilkan state error, sembunyikan konten sukses sebelumnya.
     * @param {object}  errData          - { code, message, details[] }
     * @param {number}  httpStatus       - Kode HTTP (0 = koneksi gagal)
     * @param {boolean} isConnectionError- true untuk gangguan jaringan/timeout
     */
    function showError(errData, httpStatus, isConnectionError = false) {
        stateContent.classList.add('hidden');
        stateError.classList.remove('hidden');

        // Pesan error yang mudah dipahami pengguna
        const USER_MESSAGES = {
            UNAUTHORIZED:      'Akses ditolak. Token tidak valid atau sesi sudah berakhir.',
            FORBIDDEN:         'Anda tidak memiliki izin untuk melihat rekap biaya periode ini.',
            PERIODE_NOT_FOUND: 'Periode tidak ditemukan. Pilih periode lain atau periksa UUID yang dimasukkan.',
            INVALID_PERIODE_ID:'Format periode tidak valid. Pilih dari daftar atau masukkan UUID yang benar.',
        };

        const elCode    = document.getElementById('error-code');
        const elStatus  = document.getElementById('error-http-status');
        const elMessage = document.getElementById('error-message');
        const elDetails = document.getElementById('error-details');

        if (elCode) elCode.textContent = errData.code || (httpStatus > 0 ? `HTTP ${httpStatus}` : 'Error');
        if (elStatus) elStatus.textContent = httpStatus > 0 ? `HTTP ${httpStatus}` : '';

        const userMsg = USER_MESSAGES[errData.code] || errData.message || 'Terjadi kesalahan pada permintaan rekap.';
        if (elMessage) elMessage.textContent = userMsg;

        if (elDetails) {
            elDetails.innerHTML = '';
            // Tampilkan detail hanya jika berguna untuk pengguna (bukan pesan teknis internal)
            const showableDetails = (errData.details || []).filter(d => d.message && !d.message.includes('8-4-4'));
            if (showableDetails.length) {
                showableDetails.forEach(item => {
                    const li = document.createElement('li');
                    li.className = 'text-xs text-rose-700';
                    li.textContent = item.message;
                    elDetails.appendChild(li);
                });
                elDetails.classList.remove('hidden');
            } else {
                elDetails.classList.add('hidden');
            }
        }

        // Tombol "Coba lagi" hanya untuk gangguan koneksi
        if (btnRetry) {
            btnRetry.classList.toggle('hidden', !isConnectionError);
        }
    }

    // ── Helper ────────────────────────────────────────────────────────────────

    function setText(id, text) {
        const el = document.getElementById(id);
        if (el) el.textContent = text;
    }

    // ── Inisialisasi ──────────────────────────────────────────────────────────

    async function syncDatabasePeriods() {
        try {
            const res = await fetch('/api/payroll/v1/periode', {
                headers: {
                    'Authorization': 'Bearer mock-manajemen',
                    'Accept': 'application/json',
                },
            });
            if (res.ok) {
                const data = await res.json();
                const existing = new Set(Array.from(selectPeriode.options).map(o => o.value.toLowerCase()));
                (data.data || []).forEach(p => {
                    const id = (p.uuid || p.id).toLowerCase();
                    if (!existing.has(id)) {
                        const opt = document.createElement('option');
                        opt.value = p.uuid || p.id;
                        opt.textContent = `${p.namaPeriode || 'Periode Baru'} — Dari Database`;
                        selectPeriode.appendChild(opt);
                        existing.add(id);
                    }
                });
            }
        } catch {
            // Abaikan jika offline
        }
    }

    syncDatabasePeriods();

    btnTampilkan.addEventListener('click', loadRekap);

    // Muat otomatis saat halaman pertama kali dibuka
    loadRekap();
}

// Jalankan setelah DOM siap
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initRekapBiaya);
} else {
    initRekapBiaya();
}
