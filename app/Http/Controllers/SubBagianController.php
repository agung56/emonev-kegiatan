<?php

namespace App\Http\Controllers;

use App\Models\SubBagian;
use Illuminate\Http\Request;

class SubBagianController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = SubBagian::query();

        // Fitur Search
        if ($request->has('search')) {
            $query->where('nama_sub_bagian', 'like', '%' . $request->search . '%');
        }

        $data = $query->latest()->paginate(10)->withQueryString();
        return view('admin.sub-bagian.index', compact('data'));
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
        $request->validate(['nama_sub_bagian' => 'required|unique:sub_bagians,nama_sub_bagian']);
        SubBagian::create($request->all());
        return back()->with('success', 'Data berhasil ditambahkan');
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
    public function update(Request $request, SubBagian $subBagian)
    {
        $request->validate(['nama_sub_bagian' => 'required|unique:sub_bagians,nama_sub_bagian,' . $subBagian->id]);
        $subBagian->update($request->all());
        return back()->with('success', 'Data berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SubBagian $subBagian)
    {
        $subBagian->delete();
        return back()->with('success', 'Data berhasil dihapus');
    }
}
