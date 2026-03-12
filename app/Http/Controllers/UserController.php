<?php

namespace App\Http\Controllers;

use App\Models\SubBagian;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index() {
        $users = User::with('subBagian')->get();
        $subBagians = SubBagian::all();
        return view('admin.users.index', compact('users', 'subBagians'));
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
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'role' => 'required',
            // NIP tidak required di sini sesuai kondisi Anda
        ]);

        // Jika password kosong, gunakan default 12345678
        $password = $request->password ?: '12345678';

        User::create([
            'nip' => $request->nip,
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'is_active' => $request->is_active ?? true,
            'sub_bagian_id' => $request->sub_bagian_id,
            'password' => Hash::make($password),
        ]);

        return back()->with('success', 'User berhasil ditambahkan. Password default: 12345678');
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
    public function update(Request $request, User $user) {
        $user->update($request->only(['nip', 'name', 'email', 'role', 'sub_bagian_id', 'is_active']));
        
        if ($request->password) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        return back()->with('success', 'User berhasil diperbarui');
    }

    public function toggleStatus(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);
        return back()->with('success', 'Status user berhasil diubah.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user) {
        // Proteksi User Core (ID 1 tidak bisa dihapus)
        if ($user->id === 1) {
            return back()->with('error', 'User Core sistem tidak dapat dihapus!');
        }

        $user->delete();
        return back()->with('success', 'User berhasil dihapus');
    }
}
