<?php

namespace App\Http\Controllers;

use App\Models\Rekap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RekapController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $rekap = DB::table('laporan_petugas')
            ->join('petugas', 'laporan_petugas.petugas_id', '=', 'petugas.id_petugas')
            ->select('petugas.nama_lengkap', DB::raw('COUNT(*) as total_laporan'))
            ->groupBy('petugas.id_petugas', 'petugas.nama_lengkap')
            ->orderByDesc('total_laporan')
            ->get();

        return view('rekap.index', compact('rekap'));
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
    public function show(Rekap $rekap)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Rekap $rekap)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Rekap $rekap)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Rekap $rekap)
    {
        //
    }
}
