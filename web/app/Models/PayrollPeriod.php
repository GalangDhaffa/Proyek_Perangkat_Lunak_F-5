<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PayrollPeriod extends Model
{
    use HasFactory;

    protected $table = 'payroll_periods';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'uuid',
        'nama_periode',
        'tanggal_mulai',
        'tanggal_akhir',
        'tanggal_pembayaran',
        'status',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date:Y-m-d',
        'tanggal_akhir' => 'date:Y-m-d',
        'tanggal_pembayaran' => 'date:Y-m-d',
    ];

    protected static function booted(): void
    {
        static::creating(function (PayrollPeriod $period) {
            if (empty($period->id)) {
                $period->id = ! empty($period->uuid) ? (string) $period->uuid : (string) Str::uuid();
            }
            if (empty($period->uuid)) {
                $period->uuid = (string) $period->id;
            }
            if (empty($period->status)) {
                $period->status = 'Draf';
            }
            if (empty($period->nama_periode) && ! empty($period->tanggal_mulai)) {
                $period->nama_periode = static::generateNamaPeriode($period->tanggal_mulai);
            }
        });

        static::updating(function (PayrollPeriod $period) {
            if (empty($period->uuid) && ! empty($period->id)) {
                $period->uuid = (string) $period->id;
            }
            if ($period->isDirty('tanggal_mulai') && (empty($period->nama_periode) || $period->nama_periode === static::generateNamaPeriode($period->getOriginal('tanggal_mulai')))) {
                $period->nama_periode = static::generateNamaPeriode($period->tanggal_mulai);
            }
        });
    }

    public static function generateNamaPeriode($date): string
    {
        if (empty($date)) {
            return 'Periode Baru';
        }

        $carbon = Carbon::parse($date);
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        return ($months[$carbon->month] ?? $carbon->format('F')) . ' ' . $carbon->year;
    }

    /**
     * Format konsisten untuk API response.
     */
    public function toResponseArray(): array
    {
        $id = (string) ($this->uuid ?: $this->id);
        $mulai = $this->tanggal_mulai ? Carbon::parse($this->tanggal_mulai)->format('Y-m-d') : null;
        $akhir = $this->tanggal_akhir ? Carbon::parse($this->tanggal_akhir)->format('Y-m-d') : null;
        $bayar = $this->tanggal_pembayaran ? Carbon::parse($this->tanggal_pembayaran)->format('Y-m-d') : null;

        return [
            'id' => $id,
            'periodeId' => $id,
            'namaPeriode' => $this->nama_periode ?: static::generateNamaPeriode($this->tanggal_mulai),
            'tanggalMulai' => $mulai,
            'tanggalAkhir' => $akhir,
            'tanggalPembayaran' => $bayar,
            'status' => $this->status ?? 'Draf',
            'statusPeriode' => $this->status ?? 'Draf',
            'createdAt' => $this->created_at?->timezone('+07:00')->toIso8601String(),
            'updatedAt' => $this->updated_at?->timezone('+07:00')->toIso8601String(),
        ];
    }
}
