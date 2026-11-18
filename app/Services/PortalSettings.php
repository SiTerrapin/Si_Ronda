<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class PortalSettings
{
    public const CONFIG_PATH = 'portal_settings.json';
    public const DEFAULT_HERO_BG = 'assets/images/background_desa.jpg';
    public const DEFAULT_KEGIATAN_IMG = 'assets/images/kegiatan_ronda.png';
    public const DEFAULT_TITLE = 'SiRonda — Sistem Informasi Ronda Terpadu';
    public const DEFAULT_SUBTITLE = 'Pantau jadwal jaga malam secara transparan, kenali petugas yang bersiaga malam ini, dan ikuti perkembangan situasi keamanan lingkungan kita.';

    public static function defaults(): array
    {
        return [
            'hero_background' => self::DEFAULT_HERO_BG,
            'kegiatan_image'  => self::DEFAULT_KEGIATAN_IMG,
            'hero_title'      => self::DEFAULT_TITLE,
            'hero_subtitle'   => self::DEFAULT_SUBTITLE,
        ];
    }

    public static function get(): array
    {
        $path = storage_path('app/' . self::CONFIG_PATH);
        if (!file_exists($path)) {
            return self::defaults();
        }

        $decoded = json_decode((string) file_get_contents($path), true);
        if (!is_array($decoded)) {
            return self::defaults();
        }

        return array_merge(self::defaults(), array_filter($decoded, fn ($v) => $v !== null && $v !== ''));
    }

    public static function save(array $data): void
    {
        $current = self::get();
        $merged = array_merge($current, $data);

        $dir = dirname(storage_path('app/' . self::CONFIG_PATH));
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents(
            storage_path('app/' . self::CONFIG_PATH),
            json_encode($merged, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    public static function reset(): void
    {
        $current = self::get();

        foreach (['hero_background', 'kegiatan_image'] as $key) {
            $val = $current[$key] ?? '';
            if (str_starts_with($val, 'storage/')) {
                $relative = substr($val, strlen('storage/'));
                if (Storage::disk('public')->exists($relative)) {
                    Storage::disk('public')->delete($relative);
                }
            }
        }

        $path = storage_path('app/' . self::CONFIG_PATH);
        if (file_exists($path)) {
            unlink($path);
        }
    }
}
