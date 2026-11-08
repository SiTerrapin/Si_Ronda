<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\Petugas;
use App\Models\Plot;
use Illuminate\Support\Facades\DB;


class PlotController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index()
    {
        $jadwal = Plot::with('petugas')
            ->where('is_active', '1')
            ->orderByRaw("FIELD(nama_hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu')")
            ->get();

        return view('plot.index', compact('jadwal'));
    }
    
    public function autoSchedule()
    {
        $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        $petugas = Petugas::where('is_active', '1')->inRandomOrder()->get();
        $totalSlot = count($hariList) * 3;

        if ($petugas->count() < $totalSlot) {
            return back()->with('error', 'Jumlah petugas aktif tidak cukup untuk penjadwalan.');
        }

        // Pindahkan truncate ke luar transaksi
        Plot::truncate();

        try {
            DB::beginTransaction();

            $index = 0;
            foreach ($hariList as $hari) {
                for ($i = 0; $i < 3; $i++) {
                    Plot::create([
                        'petugas_id' => $petugas[$index]->id_petugas,
                        'nama_hari' => $hari,
                        'is_active' => '1',
                        'is_leader' => $i === 0 ? '1' : '0',
                    ]);
                    $index++;
                }
            }

            DB::commit();
            return redirect()->route('plot.index')->with('success', 'Penjadwalan otomatis berhasil.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menjadwalkan: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Plot $plot)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Plot $plot)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Plot $plot)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Plot $plot)
    {
        //
    }
}
