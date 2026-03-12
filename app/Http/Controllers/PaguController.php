<?php

namespace App\Http\Controllers;

use App\Models\Pagu;
use Illuminate\Http\Request;

class PaguController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index() {
        $pagus = Pagu::with(['details'])->latest()->get();

        // Hitung total terpakai per pagu_detail dari kegiatan_anggarans
        // Filter per tahun anggaran agar tidak cross-tahun
        $pagus->each(function ($pagu) {
            $pagu->details->each(function ($detail) use ($pagu) {
                $terpakai = \App\Models\KegiatanAnggaran::where('pagu_detail_id', $detail->id)
                    ->whereHas('kegiatan', fn($q) => $q->where('tahun_anggaran', $pagu->tahun_anggaran))
                    ->sum('nominal_digunakan');
                $detail->terpakai  = (float) $terpakai;
                $detail->sisa      = (float) $detail->nominal - $terpakai;
            });
            $pagu->total_terpakai = $pagu->details->sum('terpakai');
            $pagu->sisa_pagu      = $pagu->details->sum('sisa');
        });

        return view('admin.pagu.index', compact('pagus'));
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
    public function store(Request $request) {
        $pagu = Pagu::create($request->only('kegiatan', 'tahun_anggaran', 'total_nominal', 'keterangan'));
        
        foreach ($request->details as $detail) {
            $pagu->details()->create($detail);
        }
        return back()->with('success', 'Pagu berhasil disimpan');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Pagu $pagu) {
        $pagu->update($request->only('kegiatan', 'tahun_anggaran', 'total_nominal', 'keterangan'));
        $pagu->details()->delete(); // Reset & Re-insert details
        foreach ($request->details as $detail) {
            $pagu->details()->create($detail);
        }
        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Pagu $pagu) {
        $pagu->delete();
        return back();
    }
}
