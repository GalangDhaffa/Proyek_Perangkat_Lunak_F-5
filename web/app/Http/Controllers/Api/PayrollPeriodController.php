<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PayrollPeriod;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PayrollPeriodController extends Controller
{
    private const ALLOWED_READ_ROLES = [
        'petugas_payroll',
        'admin_payroll',
        'manajemen',
        'petugas_keuangan',
        'layanan_e2',
    ];

    private const ALLOWED_WRITE_ROLES = [
        'petugas_payroll',
        'admin_payroll',
    ];

    private const CANONICAL_FIELDS = [
        'tanggalMulai',
        'tanggalAkhir',
        'tanggalPembayaran',
    ];

    /**
     * GET /api/payroll/v1/periode
     * Mengambil daftar periode payroll dari database.
     */
    public function index(Request $request): JsonResponse
    {
        $role = $this->authenticate($request);
        if ($role instanceof JsonResponse) {
            return $role;
        }

        if (! in_array($role, self::ALLOWED_READ_ROLES, true)) {
            return $this->forbidden('Pemanggil tidak memiliki izin membaca daftar periode payroll.');
        }

        $periods = PayrollPeriod::orderBy('tanggal_mulai', 'desc')->get();

        return response()->json([
            'data' => $periods->map->toResponseArray()->values()->all(),
            'total' => $periods->count(),
        ], 200);
    }

    /**
     * GET /api/payroll/v1/periode/{periodeId}
     * Membaca detail record periode payroll dari database berdasarkan UUID.
     */
    public function show(Request $request, string $periodeId): JsonResponse
    {
        $role = $this->authenticate($request);
        if ($role instanceof JsonResponse) {
            return $role;
        }

        if (! in_array($role, self::ALLOWED_READ_ROLES, true)) {
            return $this->forbidden('Pemanggil tidak memiliki izin membaca detail periode payroll.');
        }

        if (! $this->isValidUuid($periodeId)) {
            return $this->invalidPeriodId();
        }

        $period = $this->findPeriod($periodeId);
        if ($period === null) {
            return $this->periodNotFound();
        }

        return response()->json($period->toResponseArray(), 200);
    }

    /**
     * POST /api/payroll/v1/periode
     * Membuat periode payroll baru ke database. Ketiga tanggal wajib diisi.
     */
    public function store(Request $request): JsonResponse
    {
        $role = $this->authenticate($request);
        if ($role instanceof JsonResponse) {
            return $role;
        }

        if (! in_array($role, self::ALLOWED_WRITE_ROLES, true)) {
            return $this->forbidden('Hanya petugas atau admin payroll yang memiliki izin membuat periode payroll.');
        }

        $rawInput = $request->all();

        // Tolak field tambahan di luar skema kanonikal
        $extraKeys = array_diff(array_keys($rawInput), self::CANONICAL_FIELDS);
        if (! empty($extraKeys)) {
            return $this->validationError(array_map(fn ($k) => [
                'field' => (string) $k,
                'message' => "Field '$k' tidak diizinkan.",
            ], array_values($extraKeys)));
        }

        $errors = $this->validateStorePayload($rawInput);
        if (! empty($errors)) {
            return $this->validationError($errors);
        }

        $mulai = (string) $rawInput['tanggalMulai'];
        $akhir = (string) $rawInput['tanggalAkhir'];
        $bayar = (string) $rawInput['tanggalPembayaran'];

        $uuid = (string) Str::uuid();

        $period = DB::transaction(function () use ($uuid, $mulai, $akhir, $bayar) {
            return PayrollPeriod::create([
                'id' => $uuid,
                'uuid' => $uuid,
                'nama_periode' => PayrollPeriod::generateNamaPeriode($mulai),
                'tanggal_mulai' => $mulai,
                'tanggal_akhir' => $akhir,
                'tanggal_pembayaran' => $bayar,
                'status' => 'Draf',
            ]);
        });

        return response()->json($period->fresh()->toResponseArray(), 201);
    }

    /**
     * PUT /api/payroll/v1/periode/{periodeId}
     * Memperbarui seluruh field tanggal pada record yang sama (idempotent replace).
     */
    public function updateFull(Request $request, string $periodeId): JsonResponse
    {
        return $this->handleUpdate($request, $periodeId, false);
    }

    /**
     * PATCH /api/payroll/v1/periode/{periodeId}
     * Memperbarui record periode payroll (pembaruan parsial).
     */
    public function updatePartial(Request $request, string $periodeId): JsonResponse
    {
        return $this->handleUpdate($request, $periodeId, true);
    }

    /**
     * Handler update untuk PUT (seluruh field wajib) dan PATCH (parsial dengan validasi keadaan akhir).
     */
    private function handleUpdate(Request $request, string $periodeId, bool $isPatch): JsonResponse
    {
        $role = $this->authenticate($request);
        if ($role instanceof JsonResponse) {
            return $role;
        }

        if (! in_array($role, self::ALLOWED_WRITE_ROLES, true)) {
            return $this->forbidden('Hanya petugas atau admin payroll yang memiliki izin mengubah periode payroll.');
        }

        if (! $this->isValidUuid($periodeId)) {
            return $this->invalidPeriodId();
        }

        $period = $this->findPeriod($periodeId);
        if ($period === null) {
            return $this->periodNotFound();
        }

        $rawInput = $request->all();

        // Tolak field tambahan di luar skema kanonikal
        $extraKeys = array_diff(array_keys($rawInput), self::CANONICAL_FIELDS);
        if (! empty($extraKeys)) {
            return $this->validationError(array_map(fn ($k) => [
                'field' => (string) $k,
                'message' => "Field '$k' tidak diizinkan.",
            ], array_values($extraKeys)));
        }

        if ($isPatch) {
            // PATCH wajib mengirim minimal satu field kanonikal
            $providedKeys = array_intersect(array_keys($rawInput), self::CANONICAL_FIELDS);
            if (empty($providedKeys)) {
                return $this->validationError([
                    [
                        'field' => 'form',
                        'message' => 'Minimal satu field tanggal (tanggalMulai, tanggalAkhir, atau tanggalPembayaran) harus disertakan.',
                    ],
                ]);
            }
            $errors = $this->validatePatchPayload($rawInput, $period);
        } else {
            // PUT mewajibkan ketiga tanggal
            $errors = $this->validateStorePayload($rawInput);
        }

        if (! empty($errors)) {
            return $this->validationError($errors);
        }

        $existingMulai = $period->tanggal_mulai ? Carbon::parse($period->tanggal_mulai)->format('Y-m-d') : null;
        $existingAkhir = $period->tanggal_akhir ? Carbon::parse($period->tanggal_akhir)->format('Y-m-d') : null;
        $existingBayar = $period->tanggal_pembayaran ? Carbon::parse($period->tanggal_pembayaran)->format('Y-m-d') : null;

        $mulai = array_key_exists('tanggalMulai', $rawInput) ? (string) $rawInput['tanggalMulai'] : $existingMulai;
        $akhir = array_key_exists('tanggalAkhir', $rawInput) ? (string) $rawInput['tanggalAkhir'] : $existingAkhir;
        $bayar = array_key_exists('tanggalPembayaran', $rawInput) ? (string) $rawInput['tanggalPembayaran'] : $existingBayar;

        DB::transaction(function () use ($period, $mulai, $akhir, $bayar) {
            $period->fill([
                'tanggal_mulai' => $mulai,
                'tanggal_akhir' => $akhir,
                'tanggal_pembayaran' => $bayar,
                'nama_periode' => PayrollPeriod::generateNamaPeriode($mulai),
            ]);
            $period->save();
        });

        return response()->json($period->fresh()->toResponseArray(), 200);
    }

    /**
     * Validasi pembuatan periode (POST) atau update penuh (PUT).
     */
    private function validateStorePayload(array $input): array
    {
        $details = [];

        // 1. Tanggal mulai
        $mulaiErr = $this->validateDateField($input, 'tanggalMulai', 'Tanggal mulai', true);
        if ($mulaiErr !== null) {
            $details[] = $mulaiErr;
        }

        // 2. Tanggal akhir
        $akhirErr = $this->validateDateField($input, 'tanggalAkhir', 'Tanggal akhir', true);
        if ($akhirErr !== null) {
            $details[] = $akhirErr;
        }

        // 3. Tanggal pembayaran
        $bayarErr = $this->validateDateField($input, 'tanggalPembayaran', 'Tanggal pembayaran', true);
        if ($bayarErr !== null) {
            $details[] = $bayarErr;
        }

        // 4. Urutan tanggal (tanggal akhir tidak boleh sebelum tanggal mulai)
        $hasMulaiErr = collect($details)->contains('field', 'tanggalMulai');
        $hasAkhirErr = collect($details)->contains('field', 'tanggalAkhir');

        if (! $hasMulaiErr && ! $hasAkhirErr) {
            $mulai = (string) $input['tanggalMulai'];
            $akhir = (string) $input['tanggalAkhir'];
            if ($akhir < $mulai) {
                $details[] = [
                    'field' => 'tanggalAkhir',
                    'message' => 'Tanggal akhir tidak boleh sebelum tanggal mulai.',
                ];
            }
        }

        return $details;
    }

    /**
     * Validasi pembaruan parsial periode (PATCH).
     */
    private function validatePatchPayload(array $input, PayrollPeriod $current): array
    {
        $details = [];

        $existingMulai = $current->tanggal_mulai ? Carbon::parse($current->tanggal_mulai)->format('Y-m-d') : null;
        $existingAkhir = $current->tanggal_akhir ? Carbon::parse($current->tanggal_akhir)->format('Y-m-d') : null;

        $finalMulai = $existingMulai;
        $finalAkhir = $existingAkhir;

        if (array_key_exists('tanggalMulai', $input)) {
            $err = $this->validateDateField($input, 'tanggalMulai', 'Tanggal mulai', false);
            if ($err !== null) {
                $details[] = $err;
            } else {
                $finalMulai = (string) $input['tanggalMulai'];
            }
        }

        if (array_key_exists('tanggalAkhir', $input)) {
            $err = $this->validateDateField($input, 'tanggalAkhir', 'Tanggal akhir', false);
            if ($err !== null) {
                $details[] = $err;
            } else {
                $finalAkhir = (string) $input['tanggalAkhir'];
            }
        }

        if (array_key_exists('tanggalPembayaran', $input)) {
            $err = $this->validateDateField($input, 'tanggalPembayaran', 'Tanggal pembayaran', false);
            if ($err !== null) {
                $details[] = $err;
            }
        }

        // Validasi keadaan akhir
        $hasMulaiErr = collect($details)->contains('field', 'tanggalMulai');
        $hasAkhirErr = collect($details)->contains('field', 'tanggalAkhir');

        if (! $hasMulaiErr && ! $hasAkhirErr && $finalAkhir !== null && $finalMulai !== null && $finalAkhir < $finalMulai) {
            $details[] = [
                'field' => 'tanggalAkhir',
                'message' => 'Tanggal akhir tidak boleh sebelum tanggal mulai.',
            ];
        }

        return $details;
    }

    /**
     * Validasi tipe data dan kalender untuk field tanggal.
     */
    private function validateDateField(array $input, string $field, string $label, bool $required): ?array
    {
        if (! array_key_exists($field, $input)) {
            return $required ? [
                'field' => $field,
                'message' => "$label wajib diisi.",
            ] : null;
        }

        $raw = $input[$field];

        // Harus berupa string, bukan array/object/boolean/angka/null
        if (! is_string($raw)) {
            return [
                'field' => $field,
                'message' => "$label tidak valid. Gunakan format string YYYY-MM-DD.",
            ];
        }

        $val = trim($raw);

        if ($val === '') {
            return [
                'field' => $field,
                'message' => "$label wajib diisi.",
            ];
        }

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $val)) {
            return [
                'field' => $field,
                'message' => "$label tidak valid. Gunakan format YYYY-MM-DD.",
            ];
        }

        [$y, $m, $d] = explode('-', $val);
        if (! checkdate((int) $m, (int) $d, (int) $y)) {
            return [
                'field' => $field,
                'message' => "$label tidak valid.",
            ];
        }

        return null;
    }

    private function isValidUuid(string $val): bool
    {
        return preg_match('/^[0-9a-fA-F]{8}-(?:[0-9a-fA-F]{4}-){3}[0-9a-fA-F]{12}$/', $val) === 1;
    }

    private function findPeriod(string $id): ?PayrollPeriod
    {
        $normalized = strtolower($id);
        return PayrollPeriod::whereRaw('LOWER(id) = ?', [$normalized])
            ->orWhereRaw('LOWER(uuid) = ?', [$normalized])
            ->first();
    }

    /**
     * Resolusi peran berdasarkan token.
     */
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

        $role = $roles[$token] ?? null;

        if ($role === null) {
            return $this->unauthorized();
        }

        return $role;
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

    private function forbidden(string $message = 'Pemanggil tidak memiliki izin untuk operasi ini.'): JsonResponse
    {
        return response()->json([
            'code' => 'FORBIDDEN',
            'message' => $message,
            'details' => [],
        ], 403);
    }

    private function invalidPeriodId(): JsonResponse
    {
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

    private function periodNotFound(): JsonResponse
    {
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

    private function validationError(array $details): JsonResponse
    {
        return response()->json([
            'code' => 'VALIDATION_ERROR',
            'message' => 'Data periode yang dikirimkan tidak valid.',
            'details' => $details,
        ], 422);
    }
}
