<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RekapBiayaFixtureService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RekapBiayaController extends Controller
{
    public function __construct(
        private readonly RekapBiayaFixtureService $fixtureService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $token = $request->bearerToken();

        if ($token === null || $token === '') {
            return $this->unauthorized();
        }

        $role = $this->resolveRole($token);

        if ($role === null) {
            return $this->unauthorized();
        }

        if (! in_array($role, ['manajemen', 'petugas_keuangan', 'layanan_e2'], true)) {
            return $this->forbidden();
        }

        $periodeId = $this->extractPeriodoId($request);

        if ($periodeId === null) {
            return $this->invalidPeriodoId();
        }

        if (! $this->fixtureService->isValidUuid($periodeId)) {
            return $this->invalidPeriodoId();
        }

        $payload = $this->fixtureService->getByPeriodoId($periodeId);

        if ($payload === null) {
            return $this->periodNotFound();
        }

        return response()->json($payload, 200);
    }

    private function resolveRole(string $token): ?string
    {
        $roles = [
            'mock-manajemen' => 'manajemen',
            'mock-keuangan' => 'petugas_keuangan',
            'mock-e2' => 'layanan_e2',
            'mock-karyawan' => 'karyawan',
            'mock-tenaga-ahli' => 'tenaga_ahli',
        ];

        return $roles[$token] ?? null;
    }

    private function extractPeriodoId(Request $request): ?string
    {
        $rawQuery = (string) $request->server->get('QUERY_STRING', '');

        if ($rawQuery === '') {
            return null;
        }

        preg_match_all('/(?:^|&)(?:periodeId=)([^&]*)/i', $rawQuery, $matches);
        $values = array_values(array_map(static fn ($item) => urldecode($item), $matches[1] ?? []));

        if (count($values) !== 1) {
            return null;
        }

        $value = $values[0];

        if ($value === '' || trim($value) === '') {
            return null;
        }

        return $value;
    }

    private function invalidPeriodoId(): JsonResponse
    {
        return response()->json([
            'code' => 'INVALID_PERIODE_ID',
            'message' => 'periodeId wajib diisi satu kali dengan format UUID yang benar.',
            'details' => [
                [
                    'field' => 'periodeId',
                    'message' => 'Gunakan satu UUID dalam format 8-4-4-4-12.',
                ],
            ],
        ], 400);
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
            'message' => 'Pemanggil tidak memiliki izin membaca rekap biaya.',
            'details' => [],
        ], 403);
    }

    private function periodNotFound(): JsonResponse
    {
        return response()->json([
            'code' => 'PERIODE_NOT_FOUND',
            'message' => 'Periode payroll tidak ditemukan.',
            'details' => [
                [
                    'field' => 'periodeId',
                    'message' => 'UUID periode tidak dikenal.',
                ],
            ],
        ], 404);
    }
}
