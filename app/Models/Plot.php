<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plot extends Model
{
    use HasFactory;

    protected $table = 'plot_ronda';
    protected $primaryKey = 'id_plot';

    protected $fillable = [
        'petugas_id',
        'nama_hari',
        'tanggal_ronda',
        'pos_jaga',
        'shift',
        'is_leader',
        'is_active',
        'status_kehadiran',
    ];

    protected $casts = [
        'tanggal_ronda' => 'date',
    ];

    public const HARI_OFFSET = [
        'Senin'  => 0,
        'Selasa' => 1,
        'Rabu'   => 2,
        'Kamis'  => 3,
        'Jumat'  => 4,
        'Sabtu'  => 5,
        'Minggu' => 6,
    ];

    public function petugas()
    {
        return $this->belongsTo(Petugas::class, 'petugas_id', 'id_petugas');
    }

    public function laporan()
    {
        return $this->hasMany(Laporan::class, 'plot_id', 'id_plot');
    }

    /**
     * Hitung jendela waktu shift aktif yang relevan dengan waktu saat ini (Asia/Jakarta).
     * Mendukung shift lintas tengah malam (misal 22:00 - 04:00).
     */
    public function getShiftWindow(?Carbon $reference = null): array
    {
        $now = ($reference ? $reference->copy() : Carbon::now('Asia/Jakarta'))->setTimezone('Asia/Jakarta');

        $startHour = 22;
        $startMin = 0;
        $endHour = 4;
        $endMin = 0;

        if (preg_match('/(\d{2}):(\d{2})\s*-\s*(\d{2}):(\d{2})/', (string) $this->shift, $m)) {
            $startHour = (int) $m[1];
            $startMin = (int) $m[2];
            $endHour = (int) $m[3];
            $endMin = (int) $m[4];
        }

        $crossMidnight = (($endHour * 60) + $endMin) <= (($startHour * 60) + $startMin);
        $offset = self::HARI_OFFSET[$this->nama_hari] ?? 0;

        $baseDate = $this->tanggal_ronda
            ? Carbon::parse($this->tanggal_ronda->toDateString(), 'Asia/Jakarta')
            : $now->copy()->startOfWeek(Carbon::MONDAY)->addDays($offset);

        // Cek kandidat minggu berjalan dan minggu sebelumnya (untuk kasus Minggu 22:00 - Senin 04:00)
        $candidates = [$baseDate];
        if (!$this->tanggal_ronda) {
            $candidates[] = $baseDate->copy()->subWeek();
        }

        foreach ($candidates as $dayDate) {
            $start = $dayDate->copy()->setTime($startHour, $startMin, 0);
            $end = $dayDate->copy()
                ->addDays($crossMidnight ? 1 : 0)
                ->setTime($endHour, $endMin, 0);

            if ($now->between($start, $end)) {
                return [
                    'start'       => $start,
                    'end'         => $end,
                    'day_start'   => $dayDate->copy()->startOfDay(),
                    'early_start' => null,
                    'early_end'   => null,
                ];
            }
        }

        $start = $baseDate->copy()->setTime($startHour, $startMin, 0);
        $end = $baseDate->copy()
            ->addDays($crossMidnight ? 1 : 0)
            ->setTime($endHour, $endMin, 0);

        $earlyStart = $crossMidnight ? $baseDate->copy()->startOfDay() : null;
        $earlyEnd = $crossMidnight ? $baseDate->copy()->setTime($endHour, $endMin, 0) : null;

        return [
            'start'       => $start,
            'end'         => $end,
            'day_start'   => $baseDate->copy()->startOfDay(),
            'early_start' => $earlyStart,
            'early_end'   => $earlyEnd,
        ];
    }

    public function canAttendNow(?Carbon $reference = null): bool
    {
        $now = ($reference ? $reference->copy() : Carbon::now('Asia/Jakarta'))->setTimezone('Asia/Jakarta');
        $window = $this->getShiftWindow($now);

        if ($now->between($window['start'], $window['end'])) {
            return true;
        }

        if ($window['early_start'] && $window['early_end'] && $now->between($window['early_start'], $window['early_end'])) {
            return true;
        }

        return false;
    }

    public function canRequestLeaveNow(?Carbon $reference = null): bool
    {
        // Waktu pengajuan izin disamakan persis dengan waktu jam jadwal ronda
        return $this->canAttendNow($reference);
    }

    public function presensiStatusReason(?Carbon $reference = null): ?string
    {
        $now = ($reference ? $reference->copy() : Carbon::now('Asia/Jakarta'))->setTimezone('Asia/Jakarta');
        if ($this->canAttendNow($now)) {
            return null;
        }

        $window = $this->getShiftWindow($now);

        if ($now->lt($window['start'])) {
            return 'Belum masuk waktu presensi';
        }
        if ($now->gt($window['end'])) {
            return 'Waktu presensi telah berakhir';
        }

        return null;
    }
}
