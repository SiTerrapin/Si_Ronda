<?php

namespace App\Services;

use App\Models\Petugas;
use App\Models\Plot;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AutoScheduleService
{
    public const CONFIG_PATH = 'auto_schedule_config.json';

    public const HARI_OFFSET = [
        'Senin'  => 0,
        'Selasa' => 1,
        'Rabu'   => 2,
        'Kamis'  => 3,
        'Jumat'  => 4,
        'Sabtu'  => 5,
        'Minggu' => 6,
    ];

    public static function defaults(): array
    {
        return [
            'auto_rotate_enabled' => true,
            'reset_after_day'     => 'Sabtu',
            'last_rotated_cycle'  => null,
        ];
    }

    public static function getConfig(): array
    {
        $path = storage_path('app/' . self::CONFIG_PATH);
        if (!file_exists($path)) {
            return self::defaults();
        }

        $decoded = json_decode((string) file_get_contents($path), true);
        if (!is_array($decoded)) {
            return self::defaults();
        }

        return array_merge(self::defaults(), $decoded);
    }

    public static function saveConfig(array $data): array
    {
        $merged = array_merge(self::getConfig(), $data);

        $dir = dirname(storage_path('app/' . self::CONFIG_PATH));
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents(
            storage_path('app/' . self::CONFIG_PATH),
            json_encode($merged, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );

        return $merged;
    }

    /**
     * Eksekusi acak & rotasi jadwal mingguan secara proporsional.
     */
    public static function rotateNow(?Carbon $reference = null): int
    {
        $now = ($reference ? $reference->copy() : Carbon::now('Asia/Jakarta'))->setTimezone('Asia/Jakarta');
        $hariList = array_keys(self::HARI_OFFSET);
        $posList = ['Pos Utama', 'Pos Utara', 'Pos Selatan'];

        $petugas = Petugas::where('is_active', '1')->inRandomOrder()->get();
        $jumlahPetugas = $petugas->count();

        if ($jumlahPetugas === 0) {
            return 0;
        }

        $startOfWeek = $now->copy()->startOfWeek(Carbon::MONDAY);

        DB::beginTransaction();
        try {
            Plot::query()->delete();

            $index = 0;
            $created = 0;

            foreach ($hariList as $offset => $hari) {
                $tanggalHari = $startOfWeek->copy()->addDays($offset)->toDateString();
                $slotPerHari = min(3, $jumlahPetugas);

                for ($i = 0; $i < $slotPerHari; $i++) {
                    $anggota = $petugas[$index % $jumlahPetugas];
                    Plot::create([
                        'petugas_id'       => $anggota->id_petugas,
                        'nama_hari'        => $hari,
                        'tanggal_ronda'    => $tanggalHari,
                        'pos_jaga'         => $posList[$i],
                        'shift'            => 'Malam (22:00 - 04:00)',
                        'is_active'        => '1',
                        'is_leader'        => $i === 0 ? '1' : '0',
                        'status_kehadiran' => 'Terjadwal',
                    ]);
                    $index++;
                    $created++;
                }
            }

            DB::commit();
            return $created;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Cek apakah waktu saat ini sudah melewati jam berakhirnya shift pada hari reset_after_day
     * untuk siklus berjalan, dan jalankan rotasi otomatis jika belum dilakukan.
     */
    public static function checkAndRunIfDue(?Carbon $reference = null): bool
    {
        $config = self::getConfig();
        if (empty($config['auto_rotate_enabled'])) {
            return false;
        }

        $resetDay = $config['reset_after_day'] ?? 'Sabtu';
        if (!array_key_exists($resetDay, self::HARI_OFFSET)) {
            $resetDay = 'Sabtu';
        }

        $now = ($reference ? $reference->copy() : Carbon::now('Asia/Jakarta'))->setTimezone('Asia/Jakarta');
        $offset = self::HARI_OFFSET[$resetDay];

        // Tentukan tanggal reset pada minggu berjalan dan minggu sebelumnya
        $mondayThisWeek = $now->copy()->startOfWeek(Carbon::MONDAY);
        $resetDateThisWeek = $mondayThisWeek->copy()->addDays($offset);
        $endHourMinute = self::getEndTimeForDay($resetDay);

        $triggerThisWeek = $resetDateThisWeek->copy()
            ->addDays($endHourMinute['next_day'] ? 1 : 0)
            ->setTime($endHourMinute['hour'], $endHourMinute['minute'], 0);

        $targetCycle = null;
        if ($now->greaterThanOrEqualTo($triggerThisWeek)) {
            $targetCycle = $resetDateThisWeek->toDateString();
        } else {
            $resetDatePrevWeek = $resetDateThisWeek->copy()->subWeek();
            $triggerPrevWeek = $triggerThisWeek->copy()->subWeek();
            if ($now->greaterThanOrEqualTo($triggerPrevWeek)) {
                $targetCycle = $resetDatePrevWeek->toDateString();
            }
        }

        if ($targetCycle === null || ($config['last_rotated_cycle'] ?? null) === $targetCycle) {
            return false;
        }

        if (Petugas::where('is_active', '1')->count() === 0) {
            return false;
        }

        // Jadwal baru untuk minggu depan (mulai Senin berikutnya) jika reset jatuh di akhir minggu
        $nextWeekRef = $now->copy()->addDay();
        self::rotateNow($nextWeekRef);
        self::saveConfig(['last_rotated_cycle' => $targetCycle]);

        return true;
    }

    private static function getEndTimeForDay(string $hari): array
    {
        $plot = Plot::where('nama_hari', $hari)->where('is_active', '1')->first();
        $shift = $plot->shift ?? 'Malam (22:00 - 04:00)';

        if (preg_match('/(\d{2}):(\d{2})\s*-\s*(\d{2}):(\d{2})/', $shift, $m)) {
            $startMinutes = ((int) $m[1] * 60) + (int) $m[2];
            $endHour = (int) $m[3];
            $endMinute = (int) $m[4];
            $endMinutes = ($endHour * 60) + $endMinute;

            return [
                'hour'     => $endHour,
                'minute'   => $endMinute,
                'next_day' => $endMinutes <= $startMinutes,
            ];
        }

        return ['hour' => 4, 'minute' => 0, 'next_day' => true];
    }
}
