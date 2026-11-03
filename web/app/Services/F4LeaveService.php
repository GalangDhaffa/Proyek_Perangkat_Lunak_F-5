<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class F4LeaveService
{
    private string $baseUrl;
    private string $token;
    private int $timeout;
    private int $connectTimeout;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.f4.base_url', 'http://localhost:3000'), '/');
        $this->token = (string) config('services.f4.token', 'token-uji-f5');
        $this->timeout = (int) config('services.f4.timeout', 5);
        $this->connectTimeout = (int) config('services.f4.connect_timeout', 3);
    }

    /**
     * Memanggil API F-4 GET /api/v1/leave-status
     *
     * @param string $employeeId ID Karyawan (contoh: EMP-001)
     * @param string $from Tanggal awal rentang (YYYY-MM-DD, inklusif)
     * @param string $to Tanggal akhir rentang (YYYY-MM-DD, inklusif)
     * @return array
     */
    public function getLeaveStatus(string $employeeId, string $from, string $to): array
    {
        $startTime = microtime(true);
        $url = "{$this->baseUrl}/api/v1/leave-status";

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
                'Accept' => 'application/json',
            ])
            ->connectTimeout($this->connectTimeout)
            ->timeout($this->timeout)
            ->get($url, [
                'employeeId' => $employeeId,
                'from' => $from,
                'to' => $to,
            ]);

            $durationMs = (int) round((microtime(true) - $startTime) * 1000);
            $statusCode = $response->status();

            // Log pemanggilan tanpa mencatat token
            Log::info('Pemanggilan API F-4 leave-status', [
                'timestamp' => now()->toIso8601String(),
                'employee_id' => $employeeId,
                'from' => $from,
                'to' => $to,
                'http_status' => $statusCode,
                'duration_ms' => $durationMs,
            ]);

            $json = $response->json();

            if ($response->successful()) {
                return [
                    'success' => true,
                    'status' => $statusCode,
                    'data' => $json,
                ];
            }

            // Tangani status kesalahan dari F-4
            if ($statusCode === 400) {
                return [
                    'success' => false,
                    'status' => 400,
                    'code' => $json['code'] ?? 'VALIDATION_ERROR',
                    'message' => $json['message'] ?? 'Parameter permintaan tidak valid.',
                ];
            }

            if ($statusCode === 404) {
                return [
                    'success' => false,
                    'status' => 404,
                    'code' => $json['code'] ?? 'EMPLOYEE_NOT_FOUND',
                    'message' => $json['message'] ?? 'Karyawan tidak ditemukan di referensi karyawan.',
                ];
            }

            if ($statusCode === 401 || $statusCode === 403) {
                return [
                    'success' => false,
                    'status' => 502,
                    'code' => 'UPSTREAM_AUTH_ERROR',
                    'message' => 'Autentikasi ke layanan cuti F-4 ditolak. Periksa kredensial F4_TOKEN.',
                ];
            }

            return [
                'success' => false,
                'status' => $statusCode >= 500 ? 503 : $statusCode,
                'code' => $json['code'] ?? 'UPSTREAM_ERROR',
                'message' => $json['message'] ?? 'Layanan cuti F-4 mengembalikan kesalahan.',
            ];

        } catch (ConnectionException $e) {
            $durationMs = (int) round((microtime(true) - $startTime) * 1000);

            Log::warning('Gagal menghubungi API F-4 leave-status: koneksi terputus atau batas waktu terlampaui', [
                'timestamp' => now()->toIso8601String(),
                'employee_id' => $employeeId,
                'from' => $from,
                'to' => $to,
                'error' => $e->getMessage(),
                'duration_ms' => $durationMs,
            ]);

            return [
                'success' => false,
                'status' => 503,
                'code' => 'SERVICE_UNAVAILABLE',
                'message' => 'Layanan cuti F-4 tidak dapat dihubungi. Pastikan layanan F-4 berjalan.',
            ];
        } catch (\Throwable $e) {
            $durationMs = (int) round((microtime(true) - $startTime) * 1000);

            Log::error('Kesalahan tak terduga saat memanggil API F-4 leave-status', [
                'timestamp' => now()->toIso8601String(),
                'employee_id' => $employeeId,
                'from' => $from,
                'to' => $to,
                'error' => $e->getMessage(),
                'duration_ms' => $durationMs,
            ]);

            return [
                'success' => false,
                'status' => 500,
                'code' => 'INTERNAL_ERROR',
                'message' => 'Terjadi kesalahan internal saat memproses data cuti.',
            ];
        }
    }
}
