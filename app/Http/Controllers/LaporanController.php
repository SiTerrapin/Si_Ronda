<?php

namespace App\Http\Controllers;

use App\Models\Laporan;
use Illuminate\Http\Request;

class LaporanController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $laporan = Laporan::with('petugas')->orderByDesc('tanggal_lapor')->get();
        return view('laporan.index', compact('laporan'));
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
        $request->validate([
            'isi_laporan' => 'required',
        ]);

        Laporan::create([
            'petugas_id' => auth()->guard('petugas')->user()->id_petugas,
            'tanggal_lapor' => now(),
            'isi_laporan' => $request->isi_laporan,
        ]);

        return back()->with('success', 'Laporan berhasil disimpan.');
    }
    public function detail($id)
    {
        $laporan = Laporan::with('petugas')->findOrFail($id);
        return view('laporan.detail', compact('laporan'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Laporan $laporan)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Laporan $laporan)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Laporan $laporan)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Laporan $laporan)
    {
        //
    }
}
