/**
 * Rekap Biaya Frontend Module - ERP PT Anaheim Nimbus Universal (Kelompok 5 F-5)
 * Mematuhi kontrak OpenAPI v0.2 (API-K05-01 / FTR-K05-09 / UI-K05-09).
 */

// Fixture cadangan sementara jika endpoint backend Laravel belum tersedia
const FIXTURES = {
    // 1. Sukses dengan payroll dan termin
    '01a1193d-2500-7c04-854b-8bf03c711dde': {
        status: 200,
        data: {
            periodeId: "01a1193d-2500-7c04-854b-8bf03c711dde",
            namaPeriode: "Oktober 2026 (contoh fiktif)",
            tanggalMulai: "2026-10-01",
            tanggalAkhir: "2026-10-31",
            statusPeriode: "Disetujui",
            adaData: true,
            jumlahKaryawan: 1,
            totalPendapatan: 7750000.0,
            totalBonus: 750000.0,
            totalPotongan: 200000.0,
            totalBpjsKaryawan: 65000.0,
            totalGajiBersih: 7485000.0,
            totalBpjsPerusahaan: 260000.0,
            totalTermin: 25000000.0,
            jumlahTermin: 1,
            totalBiaya: null,
            diperbaruiPada: "2026-10-13T19:43:00+07:00"
        }
    },
    // 2. Periode kosong (tanpa payroll dan termin)
    '01a1193d-2500-7c05-854b-8bf03c711dde': {
        status: 200,
        data: {
            periodeId: "01a1193d-2500-7c05-854b-8bf03c711dde",
            namaPeriode: "Periode kosong (contoh fiktif)",
            tanggalMulai: "2026-10-01",
            tanggalAkhir: "2026-10-31",
            statusPeriode: "Draf",
            adaData: false,
            jumlahKaryawan: 0,
            totalPendapatan: 0,
            totalBonus: 0,
            totalPotongan: 0,
            totalBpjsKaryawan: 0,
            totalGajiBersih: 0,
            totalBpjsPerusahaan: 0,
            totalTermin: 0,
            jumlahTermin: 0,
            totalBiaya: null,
            diperbaruiPada: "2026-10-13T19:43:00+07:00"
        }
    },
    // 3. Termin saja (tanpa payroll, ada termin)
    '01a1193d-2500-7c06-854b-8bf03c711dde': {
        status: 200,
        data: {
            periodeId: "01a1193d-2500-7c06-854b-8bf03c711dde",
            namaPeriode: "Termin saja (contoh fiktif)",
            tanggalMulai: "2026-10-01",
            tanggalAkhir: "2026-10-31",
            statusPeriode: "Draf",
            adaData: true,
            jumlahKaryawan: 0,
            totalPendapatan: 0,
            totalBonus: 0,
            totalPotongan: 0,
            totalBpjsKaryawan: 0,
            totalGajiBersih: 0,
            totalBpjsPerusahaan: 0,
            totalTermin: 25000000.0,
            jumlahTermin: 1,
            totalBiaya: null,
            diperbaruiPada: "2026-10-13T19:43:00+07:00"
        }
    },
    // 4. Kasus error 404 (UUID tidak dikenal)
    '00000000-0000-0000-0000-000000000000': {
        status: 404,
        data: {
            code: "PERIODE_NOT_FOUND",
            message: "Periode payroll tidak ditemukan.",
            details: [
                {
                    field: "periodeId",
                    message: "UUID periode tidak dikenal."
                }
            ]
        }
    },
    // 5. Kasus error 400 (Format UUID tidak valid)
    'invalid-uuid-sample': {
        status: 400,
        data: {
            code: "INVALID_PERIODE_ID",
            message: "periodeId wajib diisi satu kali dengan format UUID yang benar.",
            details: [
                {
                    field: "periodeId",
                    message: "Gunakan satu UUID dalam format 8-4-4-4-12."
                }
            ]
        }
    }
};

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
    const inputCustomUuid = document.getElementById('input-custom-uuid');
    const selectAuthRole = document.getElementById('select-auth-role');
    const btnTampilkan = document.getElementById('btn-tampilkan-rekap');

    const stateLoading = document.getElementById('state-loading');
    const stateError = document.getElementById('state-error');
    const stateEmptyNotice = document.getElementById('state-empty-notice');
    const stateContent = document.getElementById('state-content');

    const badgeSource = document.getElementById('badge-source');

    if (!btnTampilkan || !selectPeriode) return;

    // Toggle custom UUID input bila dipilih opsi 'manual'
    selectPeriode.addEventListener('change', () => {
        if (selectPeriode.value === 'custom') {
            inputCustomUuid.classList.remove('hidden');
            inputCustomUuid.focus();
        } else {
            inputCustomUuid.classList.add('hidden');
        }
    });

    async function loadRekap() {
        let periodeId = selectPeriode.value;
        if (periodeId === 'custom') {
            periodeId = inputCustomUuid.value.trim();
        }

        const authRole = selectAuthRole ? selectAuthRole.value : 'manajemen';
        let token = 'mock-manajemen';
        if (authRole === 'petugas_keuangan') token = 'mock-keuangan';
        if (authRole === 'layanan_e2') token = 'mock-e2';
        if (authRole === 'unauthorized') token = 'invalid-token-demo';
        if (authRole === 'forbidden') token = 'mock-unauthorized-role';

        // Set UI loading
        stateLoading.classList.remove('hidden');
        stateError.classList.add('hidden');
        stateContent.classList.add('hidden');
        btnTampilkan.disabled = true;

        try {
            // Simulasi penolakan autentikasi lokal jika dipilih skenario khusus
            if (authRole === 'unauthorized') {
                showError({
                    code: 'UNAUTHORIZED',
                    message: 'Token tidak tersedia atau tidak valid.',
                    details: []
                }, 401, 'Simulasi Autentikasi');
                return;
            }

            if (authRole === 'forbidden') {
                showError({
                    code: 'FORBIDDEN',
                    message: 'Pemanggil tidak memiliki izin membaca rekap biaya.',
                    details: []
                }, 403, 'Simulasi Otorisasi');
                return;
            }

            // 1. Coba panggil endpoint backend Laravel resmi sesuai OpenAPI
            let usedSource = 'Backend Laravel (/api/payroll/v1/rekap-biaya)';
            let isBackendSuccess = false;
            let resultData = null;

            try {
                const response = await fetch(`/api/payroll/v1/rekap-biaya?periodeId=${encodeURIComponent(periodeId)}`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'Authorization': `Bearer ${token}`
                    }
                });

                const contentType = response.headers.get('content-type') || '';
                if (contentType.includes('application/json')) {
                    const json = await response.json();
                    if (response.ok) {
                        resultData = json;
                        isBackendSuccess = true;
                    } else {
                        // Error resmi dari backend sesuai schema ErrorResponse
                        showError(json, response.status, usedSource);
                        return;
                    }
                } else {
                    // Endpoint mengembalikan 404 HTML (route Laravel belum dibuat oleh backend)
                    throw new Error('Backend route belum aktif');
                }
            } catch (backendErr) {
                // Backend Laravel belum siap / rute belum terdaftar: Beralih ke Adapter Fixture sementara
                usedSource = 'Adapter Fixture Sementara (Backend belum siap)';
                const fixture = FIXTURES[periodeId];

                if (fixture) {
                    if (fixture.status === 200) {
                        resultData = fixture.data;
                    } else {
                        showError(fixture.data, fixture.status, usedSource);
                        return;
                    }
                } else {
                    // Default error 404 jika UUID tidak dikenal dalam fixture
                    showError({
                        code: 'PERIODE_NOT_FOUND',
                        message: 'Periode payroll tidak ditemukan.',
                        details: [{ field: 'periodeId', message: 'UUID periode tidak dikenal dalam adapter fixture.' }]
                    }, 404, usedSource);
                    return;
                }
            }

            // Render hasil sukses / empty
            renderSuccess(resultData, usedSource);

        } catch (err) {
            showError({
                code: 'UNEXPECTED_ERROR',
                message: err.message || 'Terjadi kesalahan saat memproses data.',
                details: []
            }, 500, 'Klien Frontend');
        } finally {
            stateLoading.classList.add('hidden');
            btnTampilkan.disabled = false;
        }
    }

    function renderSuccess(data, sourceLabel) {
        stateContent.classList.remove('hidden');
        stateError.classList.add('hidden');

        // Update indikator sumber data
        if (badgeSource) {
            badgeSource.textContent = sourceLabel;
            if (sourceLabel.includes('Backend')) {
                badgeSource.className = 'text-xs font-medium px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200';
            } else {
                badgeSource.className = 'text-xs font-medium px-2.5 py-1 rounded-full bg-amber-50 text-amber-700 border border-amber-200';
            }
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

    function showError(errData, httpStatus, sourceLabel) {
        stateContent.classList.add('hidden');
        stateError.classList.remove('hidden');

        if (badgeSource) {
            badgeSource.textContent = sourceLabel;
            badgeSource.className = 'text-xs font-medium px-2.5 py-1 rounded-full bg-rose-50 text-rose-700 border border-rose-200';
        }

        document.getElementById('error-code').textContent = errData.code || `HTTP_${httpStatus}`;
        document.getElementById('error-http-status').textContent = `HTTP ${httpStatus}`;
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
