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
    const btnNavKomponenGaji = document.getElementById('btn-nav-komponen-gaji');
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

    // ── Elemen Integrasi Cuti F-4 ────────────────────────────────────────────
    const sectionCuti        = document.getElementById('section-cuti');
    const inputEmployeeId    = document.getElementById('input-employee-id');
    const btnAmbilCuti       = document.getElementById('btn-ambil-cuti');
    const textTombolCuti     = document.getElementById('text-tombol-cuti');
    const iconCutiSpinner    = document.getElementById('icon-cuti-spinner');
    const iconCutiSearch     = document.getElementById('icon-cuti-search');
    const alertCutiError     = document.getElementById('alert-cuti-error');
    const textCutiError      = document.getElementById('text-cuti-error');
    const containerHasilCuti = document.getElementById('container-hasil-cuti');
    const infoRentangCuti    = document.getElementById('info-rentang-cuti');
    const badgeTotalCuti     = document.getElementById('badge-total-cuti');
    const stateCutiKosong    = document.getElementById('state-cuti-kosong');
    const stateCutiTabel     = document.getElementById('state-cuti-tabel');
    const tbodyCuti          = document.getElementById('tbody-cuti');

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
            setDisplay(btnNavKomponenGaji, 'inline-flex');
            setDisplay(wrapperPicker, 'block');

            if (current) {
                panelTitle.textContent = current.namaPeriode;
                panelBadgeStatus.textContent = current.status;
                panelKeterangan.textContent = `Periode tersimpan dengan status ${current.status}.`;

                if (btnNavKomponenGaji) {
                    const pid = current.uuid || current.id;
                    btnNavKomponenGaji.href = pid ? `/komponen-gaji?periodeId=${encodeURIComponent(pid)}` : '/komponen-gaji';
                }

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

            setDisplay(sectionCuti, 'block');

        } else if (mode === 'create') {
            breadcrumbTitle.textContent = 'Tambah periode';
            pageTitle.textContent = 'Tambah periode';
            pageSubtitle.textContent = 'Isi tanggal dan simpan perubahan periode payroll.';
            setDisplay(btnTambah, 'none');
            setDisplay(btnNavKomponenGaji, 'none');
            setDisplay(wrapperPicker, 'none');

            panelTitle.textContent = 'Data periode';
            panelBadgeStatus.textContent = 'Draf';
            panelKeterangan.textContent = 'Semua kolom wajib diisi.';

            setDisplay(btnBatal, 'inline-flex');
            setDisplay(btnSimpan, 'inline-flex');
            setDisplay(btnUbah, 'none');
            setDisplay(sectionCuti, 'none');
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
            setDisplay(btnNavKomponenGaji, 'none');
            setDisplay(wrapperPicker, 'none');

            panelTitle.textContent = 'Data periode';
            panelBadgeStatus.textContent = current ? current.status : 'Draf';
            panelKeterangan.textContent = 'Semua kolom wajib diisi.';

            setDisplay(btnBatal, 'inline-flex');
            setDisplay(btnSimpan, 'inline-flex');
            setDisplay(btnUbah, 'none');
            setDisplay(sectionCuti, 'none');
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
                let errorMsg = `Gagal memuat daftar periode dari server (HTTP ${res.status} ${res.statusText || ''}).`;
                try {
                    const errJson = await res.json();
                    if (errJson && errJson.message) {
                        errorMsg += ` ${errJson.message}`;
                    }
                } catch {
                    // Abaikan jika body respon bukan JSON
                }
                textError.textContent = errorMsg;
                setDisplay(alertError, 'flex');
            }
        } catch (err) {
            textError.textContent = `Gagal menghubungi server untuk memuat daftar periode: ${err.message}`;
            setDisplay(alertError, 'flex');
        }
    }

    // ── Event Handlers ───────────────────────────────────────────────────────

    // 1. Klik "Periode baru"
    btnTambah.addEventListener('click', () => {
        if (isSaving) return;
        setDisplay(alertSukses, 'none');
        setDisplay(alertError, 'none');
        clearLeaveResults();
        mode = 'create';
        updateView();
    });

    // 2. Klik "Ubah periode"
    btnUbah.addEventListener('click', () => {
        if (isSaving || periods.length === 0) return;
        setDisplay(alertSukses, 'none');
        setDisplay(alertError, 'none');
        clearLeaveResults();
        mode = 'edit';
        updateView();
    });

    // 3. Klik "Batal"
    btnBatal.addEventListener('click', () => {
        if (isSaving) return;
        setDisplay(alertSukses, 'none');
        setDisplay(alertError, 'none');
        clearLeaveResults();
        mode = 'view';
        updateView();
    });

    // 4. Ubah dropdown periode
    selectPeriode.addEventListener('change', (e) => {
        if (isSaving) return;
        setDisplay(alertSukses, 'none');
        setDisplay(alertError, 'none');
        clearLeaveResults();
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

    // ── Logika Integrasi Cuti F-4 ───────────────────────────────────────────

    function clearLeaveResults() {
        setDisplay(alertCutiError, 'none');
        if (textCutiError) textCutiError.textContent = '';
        setDisplay(containerHasilCuti, 'none');
        setDisplay(stateCutiKosong, 'none');
        setDisplay(stateCutiTabel, 'none');
        if (tbodyCuti) tbodyCuti.innerHTML = '';
        if (infoRentangCuti) infoRentangCuti.textContent = '';
        if (badgeTotalCuti) badgeTotalCuti.textContent = '';
    }

    function formatLeaveType(type) {
        switch (type) {
            case 'ANNUAL': return 'Tahunan (ANNUAL)';
            case 'SICK': return 'Sakit (SICK)';
            case 'MENSTRUAL': return 'Haid (MENSTRUAL)';
            case 'MATERNITY': return 'Melahirkan (MATERNITY)';
            default: return type || '-';
        }
    }

    let isLoadingCuti = false;

    async function ambilDataCuti() {
        if (isLoadingCuti) return;
        clearLeaveResults();

        const current = getSelectedPeriod();
        if (!current || !current.id) {
            textCutiError.textContent = 'Pilih periode payroll terlebih dahulu.';
            setDisplay(alertCutiError, 'flex');
            return;
        }

        if (!current.tanggalMulai || !current.tanggalAkhir) {
            textCutiError.textContent = 'Periode yang dipilih belum memiliki tanggal mulai dan tanggal akhir yang lengkap.';
            setDisplay(alertCutiError, 'flex');
            return;
        }

        const empId = inputEmployeeId ? inputEmployeeId.value.trim() : '';
        if (!empId) {
            textCutiError.textContent = 'ID Karyawan wajib diisi (contoh: EMP-001).';
            setDisplay(alertCutiError, 'flex');
            if (inputEmployeeId) inputEmployeeId.focus();
            return;
        }

        isLoadingCuti = true;
        if (btnAmbilCuti) btnAmbilCuti.disabled = true;
        setDisplay(iconCutiSpinner, 'inline-block');
        setDisplay(iconCutiSearch, 'none');
        if (textTombolCuti) textTombolCuti.textContent = 'Mengambil...';

        const url = `/api/payroll/v1/periode/${encodeURIComponent(current.id)}/cuti?employeeId=${encodeURIComponent(empId)}`;

        try {
            const res = await fetch(url, {
                headers: {
                    'Authorization': `Bearer ${AUTH_TOKEN}`,
                    'Accept': 'application/json',
                },
            });

            const data = await res.json();

            if (res.ok) {
                renderLeaveResults(data);
            } else {
                let msg = data.message || 'Gagal mengambil data cuti dari layanan F-4.';
                if (res.status === 403) {
                    msg = 'Akses ditolak: Anda tidak memiliki izin untuk mengambil data cuti karyawan.';
                } else if (res.status === 503) {
                    msg = data.message || 'Layanan cuti F-4 tidak dapat dihubungi. Pastikan layanan F-4 berjalan pada port 3000.';
                } else if (res.status === 404) {
                    msg = data.message || 'Karyawan tidak ditemukan di referensi karyawan.';
                }
                textCutiError.textContent = msg;
                setDisplay(alertCutiError, 'flex');
            }
        } catch (err) {
            textCutiError.textContent = 'Gagal menghubungi server lokal. Periksa koneksi backend Laravel.';
            setDisplay(alertCutiError, 'flex');
        } finally {
            isLoadingCuti = false;
            if (btnAmbilCuti) btnAmbilCuti.disabled = false;
            setDisplay(iconCutiSpinner, 'none');
            setDisplay(iconCutiSearch, 'inline-block');
            if (textTombolCuti) textTombolCuti.textContent = 'Ambil data cuti';
        }
    }

    function renderLeaveResults(data) {
        setDisplay(containerHasilCuti, 'block');
        if (infoRentangCuti) {
            infoRentangCuti.textContent = `${data.from} s.d. ${data.to} (${data.employeeId})`;
        }
        if (badgeTotalCuti) {
            const totalCuti = data.totalCuti || 0;
            const totalHari = data.totalHariCuti || 0;
            badgeTotalCuti.textContent = `${totalCuti} pengajuan (${totalHari} hari kerja)`;
        }

        const items = Array.isArray(data.items) ? data.items : [];

        if (items.length === 0) {
            setDisplay(stateCutiKosong, 'block');
            setDisplay(stateCutiTabel, 'none');
            if (tbodyCuti) tbodyCuti.innerHTML = '';
            return;
        }

        setDisplay(stateCutiKosong, 'none');
        setDisplay(stateCutiTabel, 'block');

        if (tbodyCuti) {
            tbodyCuti.innerHTML = '';
            items.forEach(item => {
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-slate-50/50 transition-colors';

                // Tanggal cuti
                const tanggalTd = document.createElement('td');
                tanggalTd.className = 'px-4 py-3 font-medium text-slate-900 whitespace-nowrap';
                tanggalTd.textContent = `${item.startDate} s.d. ${item.endDate}`;

                // Jenis cuti
                const jenisTd = document.createElement('td');
                jenisTd.className = 'px-4 py-3 text-slate-700 whitespace-nowrap';
                jenisTd.textContent = formatLeaveType(item.leaveType);

                // Hari kerja dalam periode
                const rangeTd = document.createElement('td');
                rangeTd.className = 'px-4 py-3 text-center font-semibold text-slate-900';
                rangeTd.textContent = `${item.workingDaysInRange ?? item.workingDays} hari`;

                // Total hari kerja pengajuan
                const totalTd = document.createElement('td');
                totalTd.className = 'px-4 py-3 text-center text-slate-500';
                totalTd.textContent = `${item.workingDays} hari`;

                // Keterangan dibayar
                const paidTd = document.createElement('td');
                paidTd.className = 'px-4 py-3 text-center';
                const paidBadge = document.createElement('span');
                paidBadge.className = item.paid
                    ? 'inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200'
                    : 'inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200';
                paidBadge.textContent = item.paid ? 'Dibayar (paid)' : 'Tidak dibayar';
                paidTd.appendChild(paidBadge);

                // Status
                const statusTd = document.createElement('td');
                statusTd.className = 'px-4 py-3 text-center';
                const statusBadge = document.createElement('span');
                statusBadge.className = 'inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200';
                statusBadge.textContent = item.status || 'APPROVED';
                statusTd.appendChild(statusBadge);

                tr.appendChild(tanggalTd);
                tr.appendChild(jenisTd);
                tr.appendChild(rangeTd);
                tr.appendChild(totalTd);
                tr.appendChild(paidTd);
                tr.appendChild(statusTd);

                tbodyCuti.appendChild(tr);
            });
        }
    }

    if (btnAmbilCuti) {
        btnAmbilCuti.addEventListener('click', ambilDataCuti);
    }

    if (inputEmployeeId) {
        inputEmployeeId.addEventListener('input', clearLeaveResults);
        inputEmployeeId.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                ambilDataCuti();
            }
        });
    }

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
