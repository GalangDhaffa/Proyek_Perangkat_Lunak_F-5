/**
 * Periode Payroll Frontend Module - ERP PT Anaheim Nimbus Universal (Kelompok 5 F-5)
 * Mendukung FTR-K05-01 dan UI-K05-01.
 * Integrasi penuh dengan REST API Laravel dan SQLite Database.
 */

export function initPeriodePayroll() {
    const root = document.getElementById('periode-payroll-app');
    if (!root) return;

    // ── Elemen DOM ──────────────────────────────────────────────────────────
    const btnTambah        = document.getElementById('btn-tambah-periode');
    const selectPeriode    = document.getElementById('select-periode');
    const wrapperPicker    = document.getElementById('wrapper-picker-periode');
    const formPeriode      = document.getElementById('form-periode');

    const breadcrumbTitle  = document.getElementById('breadcrumb-title');
    const pageTitle        = document.getElementById('page-title');
    const pageSubtitle     = document.getElementById('page-subtitle');
    const panelTitle       = document.getElementById('panel-title');
    const panelBadgeStatus = document.getElementById('panel-badge-status');
    const panelKeterangan  = document.getElementById('panel-keterangan');

    const inputMulai       = document.getElementById('input-tanggal-mulai');
    const inputAkhir       = document.getElementById('input-tanggal-akhir');
    const inputPembayaran  = document.getElementById('input-tanggal-pembayaran');

    const errorMulai       = document.getElementById('error-tanggal-mulai');
    const errorAkhir       = document.getElementById('error-tanggal-akhir');
    const errorPembayaran  = document.getElementById('error-tanggal-pembayaran');

    const btnBatal         = document.getElementById('btn-batal');
    const btnUbah          = document.getElementById('btn-ubah-periode');
    const btnSimpan        = document.getElementById('btn-simpan-periode');
    const textTombolSimpan = document.getElementById('text-tombol-simpan');
    const iconSpinner      = document.getElementById('icon-spinner');

    const alertSukses      = document.getElementById('alert-sukses');
    const textSukses       = document.getElementById('text-sukses');
    const alertError       = document.getElementById('alert-error');
    const textError        = document.getElementById('text-error');

    // ── State ───────────────────────────────────────────────────────────────
    let mode = 'view'; // 'view' | 'create' | 'edit'
    let periods = [];
    let selectedId = null;
    let originalValues = { mulai: '', akhir: '', pembayaran: '' };
    let isSaving = false;

    // Token default petugas payroll untuk demo
    const AUTH_TOKEN = 'mock-payroll';

    // ── Helper Normalisasi & Format ──────────────────────────────────────────

    function formatNamaBulan(dateStr) {
        if (!dateStr) return 'Periode Baru';
        try {
            const d = new Date(dateStr + 'T00:00:00Z');
            return new Intl.DateTimeFormat('id-ID', { month: 'long', year: 'numeric', timeZone: 'UTC' }).format(d);
        } catch {
            return dateStr;
        }
    }

    /**
     * Normalisasi data periode agar memiliki struktur field yang konsisten
     * baik dari camelCase (API) maupun snake_case (Blade/Model).
     */
    function normalizePeriod(p) {
        if (!p || typeof p !== 'object') return null;
        const id = String(p.id || p.uuid || p.periodeId || '');
        const cleanDate = s => s ? String(s).slice(0, 10) : '';

        const mulai = cleanDate(p.tanggalMulai || p.tanggal_mulai || p.start || '');
        const akhir = cleanDate(p.tanggalAkhir || p.tanggal_akhir || p.end || '');
        const bayar = cleanDate(p.tanggalPembayaran || p.tanggal_pembayaran || p.pay || '');
        const status = String(p.status || p.statusPeriode || 'Draf');

        const nama = p.namaPeriode || p.nama_periode || (mulai ? formatNamaBulan(mulai) : 'Periode Baru');

        return {
            id,
            uuid: id,
            periodeId: id,
            namaPeriode: nama,
            nama_periode: nama,
            tanggalMulai: mulai,
            tanggal_mulai: mulai,
            tanggalAkhir: akhir,
            tanggal_akhir: akhir,
            tanggalPembayaran: bayar,
            tanggal_pembayaran: bayar,
            status,
            statusPeriode: status,
        };
    }

    function setDisplay(el, displayValue) {
        if (!el) return;
        if (displayValue === 'none') {
            el.style.display = 'none';
            el.classList.add('hidden');
        } else {
            el.style.display = displayValue;
            el.classList.remove('hidden');
        }
    }

    // Baca data awal dari dataset Blade jika tersedia
    try {
        const rawInitial = root.getAttribute('data-initial-periods');
        if (rawInitial) {
            const parsed = JSON.parse(rawInitial);
            if (Array.isArray(parsed)) {
                periods = parsed.map(normalizePeriod).filter(Boolean);
            }
        }
    } catch {
        periods = [];
    }

    function getSelectedPeriod() {
        return periods.find(p => p.id === selectedId) || (periods.length > 0 ? periods[0] : null);
    }

    function clearErrors() {
        [
            [inputMulai, errorMulai],
            [inputAkhir, errorAkhir],
            [inputPembayaran, errorPembayaran],
        ].forEach(([input, errorEl]) => {
            if (input) {
                input.classList.remove('border-rose-500', 'focus:ring-rose-500', 'focus:border-rose-500');
                input.removeAttribute('aria-invalid');
            }
            if (errorEl) {
                errorEl.textContent = '';
                setDisplay(errorEl, 'none');
            }
        });
        setDisplay(alertError, 'none');
    }

    function showError(field, message) {
        let inputEl = null;
        let errorEl = null;

        if (field === 'tanggalMulai' || field === 'start') {
            inputEl = inputMulai;
            errorEl = errorMulai;
        } else if (field === 'tanggalAkhir' || field === 'end') {
            inputEl = inputAkhir;
            errorEl = errorAkhir;
        } else if (field === 'tanggalPembayaran' || field === 'pay') {
            inputEl = inputPembayaran;
            errorEl = errorPembayaran;
        }

        if (inputEl) {
            inputEl.classList.add('border-rose-500', 'focus:ring-rose-500', 'focus:border-rose-500');
            inputEl.setAttribute('aria-invalid', 'true');
        }
        if (errorEl) {
            errorEl.textContent = message;
            setDisplay(errorEl, 'block');
        }
    }

    function validateDatesClient(mulai, akhir, bayar) {
        const errors = [];
        const dateRegex = /^\d{4}-\d{2}-\d{2}$/;

        if (!mulai) {
            errors.push({ field: 'tanggalMulai', message: 'Tanggal mulai wajib diisi.' });
        } else if (!dateRegex.test(mulai)) {
            errors.push({ field: 'tanggalMulai', message: 'Tanggal mulai tidak valid.' });
        }

        if (!akhir) {
            errors.push({ field: 'tanggalAkhir', message: 'Tanggal akhir wajib diisi.' });
        } else if (!dateRegex.test(akhir)) {
            errors.push({ field: 'tanggalAkhir', message: 'Tanggal akhir tidak valid.' });
        }

        if (!bayar) {
            errors.push({ field: 'tanggalPembayaran', message: 'Tanggal pembayaran wajib diisi.' });
        } else if (!dateRegex.test(bayar)) {
            errors.push({ field: 'tanggalPembayaran', message: 'Tanggal pembayaran tidak valid.' });
        }

        const hasMulaiErr = errors.some(e => e.field === 'tanggalMulai');
        const hasAkhirErr = errors.some(e => e.field === 'tanggalAkhir');

        if (!hasMulaiErr && !hasAkhirErr && akhir < mulai) {
            errors.push({ field: 'tanggalAkhir', message: 'Tanggal akhir tidak boleh sebelum tanggal mulai.' });
        }

        return errors;
    }

    // ── Sinkronisasi UI ──────────────────────────────────────────────────────

    function syncSelectOptions() {
        if (!selectPeriode) return;
        selectPeriode.innerHTML = '';

        if (periods.length === 0) {
            const opt = document.createElement('option');
            opt.value = '';
            opt.textContent = 'Belum ada periode tersimpan';
            opt.disabled = true;
            opt.selected = true;
            selectPeriode.appendChild(opt);
            return;
        }

        periods.forEach(p => {
            const opt = document.createElement('option');
            opt.value = p.id;
            opt.textContent = p.namaPeriode;
            if (p.id === selectedId) {
                opt.selected = true;
            }
            selectPeriode.appendChild(opt);
        });
    }

    function updateView() {
        clearErrors();

        const current = getSelectedPeriod();

        if (mode === 'view') {
            breadcrumbTitle.textContent = 'Periode payroll';
            pageTitle.textContent = 'Periode payroll';
            pageSubtitle.textContent = 'Kelola periode dan siapkan data sebelum menghitung gaji.';
            setDisplay(btnTambah, 'inline-flex');
            setDisplay(wrapperPicker, 'block');

            if (current) {
                panelTitle.textContent = current.namaPeriode;
                panelBadgeStatus.textContent = current.status;
                panelKeterangan.textContent = `Periode tersimpan dengan status ${current.status}.`;

                btnUbah.disabled = false;
                btnUbah.classList.remove('opacity-50', 'cursor-not-allowed');

                inputMulai.value = current.tanggalMulai;
                inputAkhir.value = current.tanggalAkhir;
                inputPembayaran.value = current.tanggalPembayaran;
            } else {
                panelTitle.textContent = 'Belum ada periode tersimpan';
                panelBadgeStatus.textContent = 'Draf';
                panelKeterangan.textContent = 'Klik tombol Periode baru untuk membuat periode pertama.';

                btnUbah.disabled = true;
                btnUbah.classList.add('opacity-50', 'cursor-not-allowed');

                inputMulai.value = '';
                inputAkhir.value = '';
                inputPembayaran.value = '';
            }

            setDisplay(btnBatal, 'none');
            setDisplay(btnSimpan, 'none');
            setDisplay(btnUbah, 'inline-flex');

            // Readonly state
            [inputMulai, inputAkhir, inputPembayaran].forEach(inp => {
                inp.setAttribute('readonly', 'readonly');
                inp.classList.add('bg-slate-50', 'border-slate-200');
                inp.classList.remove('bg-white');
            });

        } else if (mode === 'create') {
            breadcrumbTitle.textContent = 'Tambah periode';
            pageTitle.textContent = 'Tambah periode';
            pageSubtitle.textContent = 'Isi tanggal dan simpan perubahan periode payroll.';
            setDisplay(btnTambah, 'none');
            setDisplay(wrapperPicker, 'none');

            panelTitle.textContent = 'Data periode';
            panelBadgeStatus.textContent = 'Draf';
            panelKeterangan.textContent = 'Semua kolom wajib diisi.';

            setDisplay(btnBatal, 'inline-flex');
            setDisplay(btnSimpan, 'inline-flex');
            setDisplay(btnUbah, 'none');
            textTombolSimpan.textContent = 'Simpan periode';

            // Bersihkan input untuk pembuatan baru
            inputMulai.value = '';
            inputAkhir.value = '';
            inputPembayaran.value = '';

            // Editable state
            [inputMulai, inputAkhir, inputPembayaran].forEach(inp => {
                inp.removeAttribute('readonly');
                inp.classList.remove('bg-slate-50', 'border-slate-200');
                inp.classList.add('bg-white');
            });

            inputMulai.focus();

        } else if (mode === 'edit') {
            breadcrumbTitle.textContent = 'Edit periode';
            pageTitle.textContent = 'Edit periode';
            pageSubtitle.textContent = 'Isi tanggal dan simpan perubahan periode payroll.';
            setDisplay(btnTambah, 'none');
            setDisplay(wrapperPicker, 'none');

            panelTitle.textContent = 'Data periode';
            panelBadgeStatus.textContent = current ? current.status : 'Draf';
            panelKeterangan.textContent = 'Semua kolom wajib diisi.';

            setDisplay(btnBatal, 'inline-flex');
            setDisplay(btnSimpan, 'inline-flex');
            setDisplay(btnUbah, 'none');
            textTombolSimpan.textContent = 'Simpan perubahan';

            // Simpan nilai asli sebelum edit agar tombol Batal bisa mengembalikan
            originalValues = {
                mulai: current ? current.tanggalMulai : '',
                akhir: current ? current.tanggalAkhir : '',
                pembayaran: current ? current.tanggalPembayaran : '',
            };

            inputMulai.value = originalValues.mulai;
            inputAkhir.value = originalValues.akhir;
            inputPembayaran.value = originalValues.pembayaran;

            // Editable state
            [inputMulai, inputAkhir, inputPembayaran].forEach(inp => {
                inp.removeAttribute('readonly');
                inp.classList.remove('bg-slate-50', 'border-slate-200');
                inp.classList.add('bg-white');
            });

            inputMulai.focus();
        }
    }

    // ── Panggilan API ────────────────────────────────────────────────────────

    async function fetchPeriodsFromApi() {
        try {
            const res = await fetch('/api/payroll/v1/periode', {
                headers: {
                    'Authorization': `Bearer ${AUTH_TOKEN}`,
                    'Accept': 'application/json',
                },
            });
            if (res.ok) {
                const json = await res.json();
                const fetched = (json.data || []).map(normalizePeriod).filter(Boolean);
                periods = fetched;

                if (!selectedId && periods.length > 0) {
                    selectedId = periods[0].id;
                }

                syncSelectOptions();

                // PENTING: Jangan menimpa form jika user sedang mengetik di mode tambah / edit
                if (mode === 'view') {
                    updateView();
                }
            } else {
                textError.textContent = 'Gagal memuat daftar periode dari server.';
                setDisplay(alertError, 'flex');
            }
        } catch (err) {
            textError.textContent = 'Gagal menghubungi server untuk memuat daftar periode.';
            setDisplay(alertError, 'flex');
        }
    }

    // ── Event Handlers ───────────────────────────────────────────────────────

    // 1. Klik "Periode baru"
    btnTambah.addEventListener('click', () => {
        if (isSaving) return;
        setDisplay(alertSukses, 'none');
        setDisplay(alertError, 'none');
        mode = 'create';
        updateView();
    });

    // 2. Klik "Ubah periode"
    btnUbah.addEventListener('click', () => {
        if (isSaving || periods.length === 0) return;
        setDisplay(alertSukses, 'none');
        setDisplay(alertError, 'none');
        mode = 'edit';
        updateView();
    });

    // 3. Klik "Batal"
    btnBatal.addEventListener('click', () => {
        if (isSaving) return;
        setDisplay(alertSukses, 'none');
        setDisplay(alertError, 'none');
        mode = 'view';
        updateView();
    });

    // 4. Ubah dropdown periode
    selectPeriode.addEventListener('change', (e) => {
        if (isSaving) return;
        setDisplay(alertSukses, 'none');
        setDisplay(alertError, 'none');
        selectedId = e.target.value;
        mode = 'view';
        updateView();
    });

    // 5. Submit Form (Simpan periode / Simpan perubahan)
    formPeriode.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (mode === 'view' || isSaving) return;

        setDisplay(alertSukses, 'none');
        setDisplay(alertError, 'none');
        clearErrors();

        const mulai = inputMulai.value.trim();
        const akhir = inputAkhir.value.trim();
        const bayar = inputPembayaran.value.trim();

        // Validasi sisi klien
        const clientErrors = validateDatesClient(mulai, akhir, bayar);
        if (clientErrors.length > 0) {
            clientErrors.forEach(err => showError(err.field, err.message));
            textError.textContent = 'Lengkapi kolom yang ditandai sebelum menyimpan.';
            setDisplay(alertError, 'flex');

            const firstErr = clientErrors[0];
            if (firstErr.field === 'tanggalMulai') inputMulai.focus();
            else if (firstErr.field === 'tanggalAkhir') inputAkhir.focus();
            else if (firstErr.field === 'tanggalPembayaran') inputPembayaran.focus();
            return;
        }

        // Mulai request ke backend
        isSaving = true;
        btnSimpan.disabled = true;
        btnBatal.disabled = true;
        setDisplay(iconSpinner, 'inline-block');
        const originalBtnText = textTombolSimpan.textContent;
        textTombolSimpan.textContent = 'Menyimpan...';

        const isEdit = mode === 'edit';
        const url = isEdit ? `/api/payroll/v1/periode/${selectedId}` : '/api/payroll/v1/periode';
        const method = isEdit ? 'PATCH' : 'POST';

        const payload = {
            tanggalMulai: mulai,
            tanggalAkhir: akhir,
            tanggalPembayaran: bayar,
        };

        try {
            const res = await fetch(url, {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${AUTH_TOKEN}`,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(payload),
            });

            const data = await res.json();

            if (res.ok) {
                // Sukses tersimpan ke database
                const savedPeriod = normalizePeriod(data);

                if (isEdit) {
                    const idx = periods.findIndex(p => p.id === selectedId);
                    if (idx !== -1) {
                        periods[idx] = savedPeriod;
                    }
                    textSukses.textContent = 'Perubahan periode berhasil disimpan.';
                } else {
                    periods.unshift(savedPeriod);
                    selectedId = savedPeriod.id;
                    textSukses.textContent = 'Periode berhasil disimpan.';
                }

                syncSelectOptions();
                mode = 'view';
                updateView();
                setDisplay(alertSukses, 'flex');

            } else if (res.status === 422 && Array.isArray(data.details)) {
                // Validasi gagal dari backend
                data.details.forEach(err => showError(err.field, err.message));
                textError.textContent = data.message || 'Lengkapi kolom yang ditandai sebelum menyimpan.';
                setDisplay(alertError, 'flex');

                const firstField = data.details[0]?.field;
                if (firstField === 'tanggalMulai') inputMulai.focus();
                else if (firstField === 'tanggalAkhir') inputAkhir.focus();
                else if (firstField === 'tanggalPembayaran') inputPembayaran.focus();

            } else {
                // Error lainnya dari backend
                textError.textContent = data.message || 'Gagal menyimpan periode ke database.';
                setDisplay(alertError, 'flex');
            }

        } catch (netErr) {
            textError.textContent = 'Gagal terhubung ke backend. Periksa server Laravel.';
            setDisplay(alertError, 'flex');
        } finally {
            isSaving = false;
            btnSimpan.disabled = false;
            btnBatal.disabled = false;
            setDisplay(iconSpinner, 'none');
            textTombolSimpan.textContent = originalBtnText;
        }
    });

    // ── Inisialisasi Awal ────────────────────────────────────────────────────

    if (periods.length > 0 && !selectedId) {
        selectedId = periods[0].id;
    }

    syncSelectOptions();
    updateView();
    fetchPeriodsFromApi();
}

// Jalankan saat DOM siap
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPeriodePayroll);
} else {
    initPeriodePayroll();
}
