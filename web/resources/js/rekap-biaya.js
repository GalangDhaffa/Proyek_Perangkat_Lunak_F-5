/**
 * Rekap Biaya Frontend Module - ERP PT Anaheim Nimbus Universal (Kelompok 5 F-5)
 * Mematuhi kontrak OpenAPI v0.2 (API-K05-01 / FTR-K05-09 / UI-K05-09).
 *
 * Sumber data: endpoint backend Laravel GET /api/payroll/v1/rekap-biaya
 * Backend menggunakan fixture demo (bukan integrasi F-1/F-2/F-3/F-4 nyata).
 * Autentikasi memakai token mock yang dikenali backend demo.
 */

/** Batas waktu request ke backend (ms). Mencegah loading terkunci tanpa batas. */
const REQUEST_TIMEOUT_MS = 10000;

/**
 * Format angka rupiah sesuai pedoman F-5 (pemformatan di frontend).
 */
export function formatRupiah(amount) {
    if (amount === null || amount === undefined) {
        return 'Belum ditetapkan';
    }
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 2
    }).format(amount).replace(/\s+/g, '');
}

/**
 * Format tanggal Indonesia ramah pengguna.
 */
export function formatTanggal(dateStr) {
    if (!dateStr) return '-';
    try {
        const d = new Date(dateStr);
        return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
    } catch {
        return dateStr;
    }
}

/**
 * Inisialisasi halaman Rekap Biaya.
 */
export function initRekapBiaya() {
    const selectPeriode = document.getElementById('select-periode');
    const inputCustomContainer = document.getElementById('input-custom-container');
    const inputCustomUuid = document.getElementById('input-custom-uuid');
    const selectAuthRole = document.getElementById('select-auth-role');
    const btnTampilkan = document.getElementById('btn-tampilkan-rekap');

    const stateLoading = document.getElementById('state-loading');
    const stateError = document.getElementById('state-error');
    const stateEmptyNotice = document.getElementById('state-empty-notice');
    const stateContent = document.getElementById('state-content');

    const badgeSource = document.getElementById('badge-source');

    if (!btnTampilkan || !selectPeriode) return;

    // Toggle pembungkus input UUID manual saat pilihan berubah
    selectPeriode.addEventListener('change', () => {
        if (selectPeriode.value === 'custom') {
            inputCustomContainer.classList.remove('hidden');
            if (inputCustomUuid) inputCustomUuid.focus();
        } else {
            inputCustomContainer.classList.add('hidden');
        }
    });

    async function loadRekap() {
        let periodeId = selectPeriode.value;
        if (periodeId === 'custom') {
            periodeId = inputCustomUuid ? inputCustomUuid.value.trim() : '';
        }

        // Petakan pilihan peran ke token mock yang dikenali backend
        const authRole = selectAuthRole ? selectAuthRole.value : 'manajemen';
        let token;
        if (authRole === 'manajemen') token = 'mock-manajemen';
        else if (authRole === 'petugas_keuangan') token = 'mock-keuangan';
        else if (authRole === 'layanan_e2') token = 'mock-e2';
        else if (authRole === 'unauthorized') token = 'invalid-token-demo';
        // 403: kirim token mock-karyawan yang dikenali backend sebagai peran tanpa izin
        else if (authRole === 'forbidden') token = 'mock-karyawan';
        else token = 'mock-manajemen';

        // Sembunyikan hasil sebelumnya dan tampilkan loading
        stateLoading.classList.remove('hidden');
        stateError.classList.add('hidden');
        stateContent.classList.add('hidden');
        btnTampilkan.disabled = true;

        // Update badge ke "Menghubungkan..." saat request dimulai
        if (badgeSource) {
            badgeSource.textContent = 'Menghubungkan ke backend...';
            badgeSource.className = 'text-xs font-medium px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 border border-slate-200';
        }

        const SOURCE_LABEL = 'Backend Laravel (/api/payroll/v1/rekap-biaya)';

        try {
            // Buat AbortController untuk batas waktu request
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), REQUEST_TIMEOUT_MS);

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

            // Periksa content-type. Respons HTML (mis. halaman 404 Laravel) bukan JSON valid.
            const contentType = response.headers.get('content-type') || '';
            if (!contentType.includes('application/json')) {
                showError({
                    code: 'INVALID_RESPONSE',
                    message: `Backend mengembalikan respons bukan JSON (${response.status}). Pastikan server Laravel berjalan dan route terdaftar.`,
                    details: [],
                }, response.status || 0, SOURCE_LABEL, true);
                return;
            }

            const json = await response.json();

            if (response.ok) {
                renderSuccess(json, SOURCE_LABEL);
            } else {
                // Error resmi dari backend sesuai schema ErrorResponse (400/401/403/404)
                showError(json, response.status, SOURCE_LABEL, false);
            }

        } catch (err) {
            // Koneksi gagal, batas waktu habis, atau kesalahan jaringan — tampilkan kegagalan nyata
            let message;
            if (err.name === 'AbortError') {
                message = `Request timeout setelah ${REQUEST_TIMEOUT_MS / 1000} detik. Periksa apakah server Laravel berjalan di port 8001.`;
            } else {
                message = `Koneksi ke backend gagal: ${err.message || 'Network error'}. Pastikan server Laravel berjalan di http://127.0.0.1:8001.`;
            }
            showError({
                code: 'CONNECTION_ERROR',
                message,
                details: [],
            }, 0, SOURCE_LABEL, true);
        } finally {
            stateLoading.classList.add('hidden');
            btnTampilkan.disabled = false;
        }
    }

    function renderSuccess(data, sourceLabel) {
        stateContent.classList.remove('hidden');
        stateError.classList.add('hidden');

        // Update indikator sumber data — hijau untuk backend berhasil
        if (badgeSource) {
            badgeSource.textContent = sourceLabel;
            badgeSource.className = 'text-xs font-medium px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200';
        }

        // Teks periode header & status
        document.getElementById('view-nama-periode').textContent = data.namaPeriode;
        document.getElementById('view-tanggal-rentang').textContent = `${formatTanggal(data.tanggalMulai)} s.d. ${formatTanggal(data.tanggalAkhir)}`;
        document.getElementById('view-diperbarui-pada').textContent = data.diperbaruiPada ? data.diperbaruiPada.replace('T', ' ').substring(0, 19) + ' WIB' : '-';
        document.getElementById('view-periode-id').textContent = data.periodeId;

        // Badge Status Periode
        const badgeStatus = document.getElementById('view-status-periode');
        badgeStatus.textContent = data.statusPeriode;
        if (data.statusPeriode === 'Disetujui') {
            badgeStatus.className = 'px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200';
        } else if (data.statusPeriode === 'Dihitung') {
            badgeStatus.className = 'px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200';
        } else if (data.statusPeriode === 'Menunggu Persetujuan') {
            badgeStatus.className = 'px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200';
        } else {
            badgeStatus.className = 'px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200';
        }

        // Banner Periode Kosong (adaData: false)
        if (!data.adaData) {
            stateEmptyNotice.classList.remove('hidden');
        } else {
            stateEmptyNotice.classList.add('hidden');
        }

        // 3 Stat Cards Utama (Sesuai Figma)
        document.getElementById('stat-pendapatan').textContent = formatRupiah(data.totalPendapatan);
        document.getElementById('stat-pendapatan-subtext').textContent = `Termasuk bonus ${formatRupiah(data.totalBonus)}`;

        document.getElementById('stat-potongan').textContent = formatRupiah(data.totalPotongan);
        document.getElementById('stat-potongan-subtext').textContent = `BPJS karyawan ${formatRupiah(data.totalBpjsKaryawan)} dicatat terpisah`;

        document.getElementById('stat-gajibersih').textContent = formatRupiah(data.totalGajiBersih);

        // Tabel Rincian Biaya (17 Field Agregat)
        document.getElementById('row-pendapatan').textContent = formatRupiah(data.totalPendapatan);
        document.getElementById('row-pendapatan-sumber').textContent = `Hasil payroll (${data.jumlahKaryawan} karyawan)`;

        document.getElementById('row-bonus').textContent = formatRupiah(data.totalBonus);
        document.getElementById('row-potongan').textContent = formatRupiah(data.totalPotongan);
        document.getElementById('row-bpjs-karyawan').textContent = formatRupiah(data.totalBpjsKaryawan);
        document.getElementById('row-gaji-bersih').textContent = formatRupiah(data.totalGajiBersih);
        document.getElementById('row-bpjs-perusahaan').textContent = formatRupiah(data.totalBpjsPerusahaan);

        document.getElementById('row-termin').textContent = formatRupiah(data.totalTermin);
        document.getElementById('row-termin-sumber').textContent = `Kontrak contoh · Terpisah (${data.jumlahTermin} termin)`;

        // Total biaya: WAJIB tampil 'Belum ditetapkan' jika null sesuai kontrak draf v0.2
        const totalBiayaEl = document.getElementById('row-total-biaya');
        if (data.totalBiaya === null) {
            totalBiayaEl.textContent = 'Belum ditetapkan';
            totalBiayaEl.className = 'text-slate-500 font-medium italic';
        } else {
            totalBiayaEl.textContent = formatRupiah(data.totalBiaya);
            totalBiayaEl.className = 'text-slate-900 font-bold';
        }

        // Field Metadata
        document.getElementById('meta-ada-data').textContent = data.adaData ? 'Tersedia' : 'Kosong (false)';
        document.getElementById('meta-jumlah-karyawan').textContent = `${data.jumlahKaryawan} orang`;
        document.getElementById('meta-jumlah-termin').textContent = `${data.jumlahTermin} termin`;
    }

    /**
     * Tampilkan state error dan sembunyikan konten sukses sebelumnya.
     * @param {object} errData - Objek error dari backend (code, message, details)
     * @param {number} httpStatus - Kode status HTTP
     * @param {string} sourceLabel - Label sumber data untuk badge
     * @param {boolean} isConnectionError - true jika kegagalan di sisi koneksi/jaringan
     */
    function showError(errData, httpStatus, sourceLabel, isConnectionError = false) {
        // Pastikan konten sukses sebelumnya disembunyikan
        stateContent.classList.add('hidden');
        stateError.classList.remove('hidden');

        if (badgeSource) {
            badgeSource.textContent = sourceLabel;
            if (isConnectionError) {
                badgeSource.className = 'text-xs font-medium px-2.5 py-1 rounded-full bg-orange-50 text-orange-700 border border-orange-200';
            } else {
                badgeSource.className = 'text-xs font-medium px-2.5 py-1 rounded-full bg-rose-50 text-rose-700 border border-rose-200';
            }
        }

        document.getElementById('error-code').textContent = errData.code || `HTTP_${httpStatus}`;
        document.getElementById('error-http-status').textContent = httpStatus > 0 ? `HTTP ${httpStatus}` : 'Koneksi gagal';
        document.getElementById('error-message').textContent = errData.message || 'Terjadi kesalahan pada permintaan rekap.';

        const detailsEl = document.getElementById('error-details');
        detailsEl.innerHTML = '';
        if (errData.details && errData.details.length > 0) {
            errData.details.forEach(item => {
                const li = document.createElement('li');
                li.className = 'text-xs text-rose-700';
                li.textContent = item.field ? `[${item.field}] ${item.message}` : item.message;
                detailsEl.appendChild(li);
            });
            detailsEl.classList.remove('hidden');
        } else {
            detailsEl.classList.add('hidden');
        }
    }

    btnTampilkan.addEventListener('click', loadRekap);

    // Otomatis muat saat pertama kali halaman terbuka
    loadRekap();
}

// Inisialisasi saat DOM siap
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initRekapBiaya);
} else {
    initRekapBiaya();
}
