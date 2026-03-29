<?php

namespace App\Http\Controllers;

use App\Models\Indikator;
use App\Models\Sasaran;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class SasaranController extends Controller
{
    public function index()
    {
        $sasurans = Sasaran::with('indikators')->latest()->get();
        return view('admin.sasaran.index', compact('sasurans'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateSasaran($request);
        $sasaran = Sasaran::create(Arr::only($validated, [
            'nama_sasaran',
            'kepemilikan',
            'tahun_anggaran',
            'is_aktif',
        ]));
        $this->syncIndikators($sasaran, $validated['indikators']);

        return back()->with('success', 'Sasaran berhasil disimpan');
    }

    public function update(Request $request, Sasaran $sasaran)
    {
        $validated = $this->validateSasaran($request);
        $sasaran->update(Arr::only($validated, [
            'nama_sasaran',
            'kepemilikan',
            'tahun_anggaran',
            'is_aktif',
        ]));
        $this->syncIndikators($sasaran, $validated['indikators']);

        return back()->with('success', 'Sasaran berhasil diperbarui');
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

    private function validateSasaran(Request $request): array
    {
        return $request->validate([
            'nama_sasaran'                => 'required|string|max:255',
            'kepemilikan'                 => 'required|in:lembaga,sekretariat',
            'tahun_anggaran'              => 'required|digits:4|integer',
            'is_aktif'                    => 'required|boolean',
            'indikators'                  => 'required|array|min:1',
            'indikators.*.id'             => 'nullable|integer',
            'indikators.*.nama_indikator' => 'required|string|max:255',
            'indikators.*.target'         => 'required|numeric|min:0',
            'indikators.*.satuan'         => 'required|in:' . implode(',', Indikator::SATUAN_OPTIONS),
        ]);
    }

    private function syncIndikators(Sasaran $sasaran, array $indikators): void
    {
        $existingIndikators = $sasaran->indikators()->get()->keyBy('id');
        $retainedIds = [];

        foreach ($indikators as $indikator) {
            $payload = Arr::only($indikator, ['nama_indikator', 'target', 'satuan']);
            $indikatorId = isset($indikator['id']) ? (int) $indikator['id'] : null;

            if ($indikatorId && $existingIndikators->has($indikatorId)) {
                $existingIndikators[$indikatorId]->update($payload);
                $retainedIds[] = $indikatorId;
                continue;
            }

            $retainedIds[] = $sasaran->indikators()->create($payload)->id;
        }

        $sasaran->indikators()->whereNotIn('id', $retainedIds)->delete();
    }
}