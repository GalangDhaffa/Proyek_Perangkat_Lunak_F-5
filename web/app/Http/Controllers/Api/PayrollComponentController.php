<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PayrollComponent;
use App\Models\PayrollPeriod;
use App\Services\F4LeaveService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PayrollComponentController extends Controller
{
    private const ALLOWED_ROLES = [
        'petugas_payroll',
        'admin_payroll',
    ];

    private const ALLOWED_PAYLOAD_FIELDS = [
        'employeeId',
        'gajiPokok',
        'bonus',
        'bpjsKaryawan',
        'bpjsPerusahaan',
        'versiKomponen',
        'komponen',
    ];

    public function show(Request $request, string $periodeId): JsonResponse
    {
        $role = $this->authenticate($request);
        if ($role instanceof JsonResponse) {
            return $role;
        }

        if (! in_array($role, self::ALLOWED_ROLES, true)) {
            return $this->forbidden();
        }

        $period = $this->findPeriod($periodeId);
        if ($period instanceof JsonResponse) {
            return $period;
        }

        $employeeId = $request->query('employeeId');
        if (! is_string($employeeId) || trim($employeeId) === '') {
            return response()->json([
                'code' => 'VALIDATION_ERROR',
                'message' => 'Parameter employeeId wajib diisi.',
                'details' => [
                    [
                        'field' => 'employeeId',
                        'message' => 'ID karyawan wajib diisi (contoh: EMP-001).',
                    ],
                ],
            ], 400);
        }

        $employeeId = trim($employeeId);
        if (! $this->isValidEmployeeId($employeeId)) {
            return $this->invalidEmployeeId();
        }

        $component = $period->payrollComponents()
            ->where('employee_id', $employeeId)
            ->first();

        if ($component === null) {
            return $this->componentNotFound();
        }

        return response()->json($this->componentPayload($period, $component), 200);
    }

    public function update(Request $request, string $periodeId): JsonResponse
    {
        $role = $this->authenticate($request);
        if ($role instanceof JsonResponse) {
            return $role;
        }

        if (! in_array($role, self::ALLOWED_ROLES, true)) {
            return $this->forbidden();
        }

        $period = $this->findPeriod($periodeId);
        if ($period instanceof JsonResponse) {
            return $period;
        }

        $unknownFields = array_diff(array_keys($request->all()), self::ALLOWED_PAYLOAD_FIELDS);
        if ($unknownFields !== []) {
            return $this->validationError(array_map(
                static fn (string $field): array => [
                    'field' => $field,
                    'message' => "Field '$field' tidak diizinkan.",
                ],
                array_values($unknownFields),
            ));
        }

        $validator = Validator::make($request->all(), [
            'employeeId' => ['required', 'string', 'max:64', 'regex:/^[A-Za-z0-9-]+$/'],
            'gajiPokok' => ['required', 'numeric', 'min:0', 'max:9999999999999.99', 'decimal:0,2'],
            'bonus' => ['required', 'numeric', 'min:0', 'max:9999999999999.99', 'decimal:0,2'],
            'bpjsKaryawan' => ['required', 'numeric', 'min:0', 'max:9999999999999.99', 'decimal:0,2'],
            'bpjsPerusahaan' => ['required', 'numeric', 'min:0', 'max:9999999999999.99', 'decimal:0,2'],
            'versiKomponen' => ['required', 'integer', 'min:1'],
            'komponen' => ['required', 'array'],
            'komponen.*' => ['required', 'array:nama,jenis,nominal,aktif'],
            'komponen.*.nama' => ['required', 'string', 'max:100'],
            'komponen.*.jenis' => ['required', 'string', 'in:pendapatan,potongan'],
            'komponen.*.nominal' => ['required', 'numeric', 'min:0', 'max:9999999999999.99', 'decimal:0,2'],
            'komponen.*.aktif' => ['required', 'boolean'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($this->validationDetails($validator->errors()->messages()));
        }

        $data = $validator->validated();
        $data['employeeId'] = trim($data['employeeId']);
        $data['komponen'] = array_map(
            fn (array $component): array => [
                'nama' => $component['nama'],
                'jenis' => $component['jenis'],
                'nominal' => $this->centsToAmount($this->amountToCents($component['nominal'])),
                'aktif' => filter_var($component['aktif'], FILTER_VALIDATE_BOOLEAN),
            ],
            $data['komponen'],
        );

        $component = DB::transaction(function () use ($period, $data): PayrollComponent {
            return PayrollComponent::query()->updateOrCreate(
                [
                    'payroll_period_id' => $period->id,
                    'employee_id' => $data['employeeId'],
                ],
                [
                    'gaji_pokok' => $data['gajiPokok'],
                    'bonus' => $data['bonus'],
                    'bpjs_karyawan' => $data['bpjsKaryawan'],
                    'bpjs_perusahaan' => $data['bpjsPerusahaan'],
                    'komponen' => $data['komponen'],
                    'versi_komponen' => $data['versiKomponen'],
                ],
            );
        });

        return response()->json($this->componentPayload($period, $component), 200);
    }

    public function preview(Request $request, string $periodeId, F4LeaveService $leaveService): JsonResponse
    {
        $role = $this->authenticate($request);
        if ($role instanceof JsonResponse) {
            return $role;
        }

        if (! in_array($role, self::ALLOWED_ROLES, true)) {
            return $this->forbidden();
        }

        $period = $this->findPeriod($periodeId);
        if ($period instanceof JsonResponse) {
            return $period;
        }

        $unknownFields = array_diff(array_keys($request->all()), ['employeeId']);
        if ($unknownFields !== []) {
            return $this->validationError(array_map(
                static fn (string $field): array => [
                    'field' => $field,
                    'message' => "Field '$field' tidak diizinkan.",
                ],
                array_values($unknownFields),
            ));
        }

        $validator = Validator::make($request->all(), [
            'employeeId' => ['required', 'string', 'max:64', 'regex:/^[A-Za-z0-9-]+$/'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($this->validationDetails($validator->errors()->messages()));
        }

        $employeeId = trim($validator->validated()['employeeId']);
        $component = $period->payrollComponents()
            ->where('employee_id', $employeeId)
            ->first();

        if ($component === null) {
            return $this->componentNotFound();
        }

        if ($period->tanggal_mulai === null || $period->tanggal_akhir === null) {
            return response()->json([
                'code' => 'INVALID_PERIODE_DATES',
                'message' => 'Tanggal periode belum lengkap pada database.',
                'details' => [],
            ], 400);
        }

        $from = Carbon::parse($period->tanggal_mulai)->format('Y-m-d');
        $to = Carbon::parse($period->tanggal_akhir)->format('Y-m-d');
        if ($to < $from) {
            return response()->json([
                'code' => 'INVALID_DATE_RANGE',
                'message' => 'Tanggal akhir periode tidak boleh sebelum tanggal mulai.',
                'details' => [],
            ], 400);
        }

        $days = (int) Carbon::parse($from)->diffInDays(Carbon::parse($to)) + 1;
        if ($days > 31) {
            return response()->json([
                'code' => 'RANGE_TOO_LONG',
                'message' => "Rentang tanggal periode ({$days} hari) melebihi batas maksimal 31 hari inklusif layanan F-4.",
                'details' => [
                    [
                        'field' => 'rentangTanggal',
                        'message' => 'Layanan F-4 membatasi rentang tanggal status cuti maksimal 31 hari inklusif.',
                    ],
                ],
            ], 400);
        }

        $leaveResult = $leaveService->getLeaveStatus($employeeId, $from, $to);
        if (! $leaveResult['success']) {
            return response()->json([
                'code' => $leaveResult['code'] ?? 'UPSTREAM_ERROR',
                'message' => $leaveResult['message'] ?? 'Gagal mengambil data cuti dari layanan F-4.',
                'details' => [],
            ], $leaveResult['status'] ?? 503);
        }

        $leaveData = $leaveResult['data'] ?? null;
        $leaveItems = is_array($leaveData) ? ($leaveData['items'] ?? null) : null;
        if (! is_array($leaveItems)) {
            return response()->json([
                'code' => 'INVALID_UPSTREAM_RESPONSE',
                'message' => 'Format respons data cuti dari layanan F-4 tidak valid.',
                'details' => [],
            ], 502);
        }

        $workingDays = 0;
        foreach ($leaveItems as $item) {
            if (
                ! is_array($item)
                || ! isset($item['workingDaysInRange'])
                || ! is_numeric($item['workingDaysInRange'])
                || (float) $item['workingDaysInRange'] < 0
                || floor((float) $item['workingDaysInRange']) !== (float) $item['workingDaysInRange']
            ) {
                return response()->json([
                    'code' => 'INVALID_UPSTREAM_RESPONSE',
                    'message' => 'Format respons data cuti dari layanan F-4 tidak valid.',
                    'details' => [],
                ], 502);
            }

            $workingDays += (int) $item['workingDaysInRange'];
        }

        $activeComponents = array_filter(
            $component->komponen,
            static fn (array $item): bool => $item['aktif'] === true,
        );
        $incomeComponents = 0;
        $deductionComponents = 0;

        foreach ($activeComponents as $item) {
            if ($item['jenis'] === 'pendapatan') {
                $incomeComponents += $this->amountToCents($item['nominal']);
            } else {
                $deductionComponents += $this->amountToCents($item['nominal']);
            }
        }

        $baseSalary = $this->amountToCents($component->gaji_pokok);
        $bonus = $this->amountToCents($component->bonus);
        $employeeBpjs = $this->amountToCents($component->bpjs_karyawan);
        $employerBpjs = $this->amountToCents($component->bpjs_perusahaan);
        $income = $baseSalary + $incomeComponents + $bonus;
        $netSalary = $income - $deductionComponents - $employeeBpjs;

        return response()->json([
            'periodeId' => (string) ($period->uuid ?: $period->id),
            'employeeId' => $employeeId,
            'versiKomponen' => $component->versi_komponen,
            'gajiPokok' => $this->centsToAmount($baseSalary),
            'bonus' => $this->centsToAmount($bonus),
            'bpjsKaryawan' => $this->centsToAmount($employeeBpjs),
            'bpjsPerusahaan' => $this->centsToAmount($employerBpjs),
            'komponenPendapatan' => $this->centsToAmount($incomeComponents),
            'komponenPotongan' => $this->centsToAmount($deductionComponents),
            'totalPendapatan' => $this->centsToAmount($income),
            'totalPotongan' => $this->centsToAmount($deductionComponents),
            'totalGajiBersih' => $this->centsToAmount($netSalary),
            'cuti' => [
                'from' => $from,
                'to' => $to,
                'items' => $leaveItems,
                'totalCuti' => count($leaveItems),
                'totalHariCuti' => $workingDays,
            ],
        ], 200);
    }

    private function componentPayload(PayrollPeriod $period, PayrollComponent $component): array
    {
        return [
            'periodeId' => (string) ($period->uuid ?: $period->id),
            'employeeId' => $component->employee_id,
            'gajiPokok' => $this->centsToAmount($this->amountToCents($component->gaji_pokok)),
            'bonus' => $this->centsToAmount($this->amountToCents($component->bonus)),
            'bpjsKaryawan' => $this->centsToAmount($this->amountToCents($component->bpjs_karyawan)),
            'bpjsPerusahaan' => $this->centsToAmount($this->amountToCents($component->bpjs_perusahaan)),
            'versiKomponen' => $component->versi_komponen,
            'komponen' => $component->komponen,
            'diperbaruiPada' => $component->updated_at?->timezone('+07:00')->toIso8601String(),
        ];
    }

    private function amountToCents(int|float|string $amount): int
    {
        return (int) round((float) $amount * 100);
    }

    private function centsToAmount(int $amount): int|float
    {
        return $amount % 100 === 0 ? intdiv($amount, 100) : round($amount / 100, 2);
    }

    private function authenticate(Request $request): string|JsonResponse
    {
        $token = $request->bearerToken();
        if ($token === null || $token === '') {
            return $this->unauthorized();
        }

        $roles = [
            'mock-payroll' => 'petugas_payroll',
            'mock-petugas-payroll' => 'petugas_payroll',
            'mock-admin-payroll' => 'admin_payroll',
            'mock-manajemen' => 'manajemen',
            'mock-keuangan' => 'petugas_keuangan',
            'mock-e2' => 'layanan_e2',
            'mock-karyawan' => 'karyawan',
            'mock-tenaga-ahli' => 'tenaga_ahli',
        ];

        return $roles[$token] ?? $this->unauthorized();
    }

    private function findPeriod(string $periodeId): PayrollPeriod|JsonResponse
    {
        if (! $this->isValidUuid($periodeId)) {
            return response()->json([
                'code' => 'INVALID_PERIODE_ID',
                'message' => 'ID periode tidak valid. Gunakan format UUID yang benar.',
                'details' => [
                    [
                        'field' => 'periodeId',
                        'message' => 'Gunakan satu UUID dalam format 8-4-4-4-12.',
                    ],
                ],
            ], 400);
        }

        $normalizedId = strtolower($periodeId);
        $period = PayrollPeriod::query()
            ->whereRaw('LOWER(id) = ?', [$normalizedId])
            ->orWhereRaw('LOWER(uuid) = ?', [$normalizedId])
            ->first();

        if ($period === null) {
            return response()->json([
                'code' => 'PERIODE_NOT_FOUND',
                'message' => 'Periode payroll tidak ditemukan.',
                'details' => [
                    [
                        'field' => 'periodeId',
                        'message' => 'UUID periode tidak dikenal di database.',
                    ],
                ],
            ], 404);
        }

        return $period;
    }

    private function isValidUuid(string $value): bool
    {
        return preg_match('/^[0-9a-fA-F]{8}-(?:[0-9a-fA-F]{4}-){3}[0-9a-fA-F]{12}$/', $value) === 1;
    }

    private function isValidEmployeeId(string $employeeId): bool
    {
        return preg_match('/^[A-Za-z0-9-]+$/', $employeeId) === 1 && strlen($employeeId) <= 64;
    }

    private function unauthorized(): JsonResponse
    {
        return response()->json([
            'code' => 'UNAUTHORIZED',
            'message' => 'Token tidak tersedia atau tidak valid.',
            'details' => [],
        ], 401, [
            'WWW-Authenticate' => 'Bearer',
        ]);
    }

    private function forbidden(): JsonResponse
    {
        return response()->json([
            'code' => 'FORBIDDEN',
            'message' => 'Hanya petugas atau admin payroll yang memiliki izin mengelola komponen dan pratinjau payroll.',
            'details' => [],
        ], 403);
    }

    private function invalidEmployeeId(string $message = 'employeeId tidak valid. Gunakan format ID referensi karyawan yang benar, contoh EMP-001.'): JsonResponse
    {
        return response()->json([
            'code' => 'INVALID_EMPLOYEE_ID',
            'message' => $message,
            'details' => [
                [
                    'field' => 'employeeId',
                    'message' => 'ID karyawan wajib diisi dengan huruf, angka, dan tanda hubung maksimal 64 karakter.',
                ],
            ],
        ], 400);
    }

    private function componentNotFound(): JsonResponse
    {
        return response()->json([
            'code' => 'PAYROLL_COMPONENT_NOT_FOUND',
            'message' => 'Komponen payroll untuk karyawan pada periode tersebut tidak ditemukan.',
            'details' => [
                [
                    'field' => 'employeeId',
                    'message' => 'Simpan komponen payroll terlebih dahulu sebelum membaca atau menghitung pratinjau.',
                ],
            ],
        ], 404);
    }

    private function validationDetails(array $messages): array
    {
        $details = [];
        foreach ($messages as $field => $fieldMessages) {
            foreach ($fieldMessages as $message) {
                $details[] = [
                    'field' => $field,
                    'message' => $message,
                ];
            }
        }

        return $details;
    }

    private function validationError(array $details): JsonResponse
    {
        return response()->json([
            'code' => 'VALIDATION_ERROR',
            'message' => 'Data komponen payroll yang dikirimkan tidak valid.',
            'details' => $details,
        ], 422);
    }
}
