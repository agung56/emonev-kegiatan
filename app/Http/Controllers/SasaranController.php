<?php

namespace App\Http\Controllers;

use App\Models\Sasaran;
use Illuminate\Http\Request;

class SasaranController extends Controller
{
    public function index()
    {
        $sasurans = Sasaran::with('indikators')->latest()->get();
        return view('admin.sasaran.index', compact('sasurans'));
    }

    public function store(Request $request)
    {
        $sasaran = Sasaran::create($request->only('nama_sasaran', 'kepemilikan', 'tahun_anggaran', 'is_aktif'));

        foreach ($request->indikators as $indikator) {
            $sasaran->indikators()->create($indikator);
        }

        return back()->with('success', 'Sasaran berhasil disimpan');
    }

    public function update(Request $request, Sasaran $sasaran)
    {
        $sasaran->update($request->only('nama_sasaran', 'kepemilikan', 'tahun_anggaran', 'is_aktif'));

        $sasaran->indikators()->delete(); // Reset & Re-insert
        foreach ($request->indikators as $indikator) {
            $sasaran->indikators()->create($indikator);
        }

        return back();
    }

    public function toggleStatus(Sasaran $sasaran)
    {
        $sasaran->update(['is_aktif' => !$sasaran->is_aktif]);
        return back()->with('success', 'Status sasaran berhasil diubah.');
    }

    public function destroy(Sasaran $sasaran)
    {
        $sasaran->delete();
        return back();
    }
}