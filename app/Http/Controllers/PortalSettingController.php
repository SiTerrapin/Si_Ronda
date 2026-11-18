<?php

namespace App\Http\Controllers;

use App\Services\PortalSettings;
use Illuminate\Http\Request;

class PortalSettingController extends Controller
{
    public function index()
    {
        $settings = PortalSettings::get();
        return view('portal.settings', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'hero_title'      => ['nullable', 'string', 'max:120'],
            'hero_subtitle'   => ['nullable', 'string', 'max:350'],
            'hero_background' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'kegiatan_image'  => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $updates = [];
        if (array_key_exists('hero_title', $validated) && trim((string) $validated['hero_title']) !== '') {
            $updates['hero_title'] = trim($validated['hero_title']);
        }
        if (array_key_exists('hero_subtitle', $validated) && trim((string) $validated['hero_subtitle']) !== '') {
            $updates['hero_subtitle'] = trim($validated['hero_subtitle']);
        }

        if ($request->hasFile('hero_background')) {
            $path = $request->file('hero_background')->store('portal', 'public');
            $updates['hero_background'] = 'storage/' . $path;
        }

        if ($request->hasFile('kegiatan_image')) {
            $path = $request->file('kegiatan_image')->store('portal', 'public');
            $updates['kegiatan_image'] = 'storage/' . $path;
        }

        PortalSettings::save($updates);

        return redirect()->route('portal.settings')->with('success', 'Pengaturan gambar & banner Portal Warga berhasil diperbarui.');
    }

    public function reset()
    {
        PortalSettings::reset();

        return redirect()->route('portal.settings')->with('success', 'Gambar & teks Portal Warga berhasil dikembalikan ke pengaturan default.');
    }
}
