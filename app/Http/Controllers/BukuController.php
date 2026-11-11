<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Kategori;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BukuController extends Controller
{
    public function index(Request $request): View
    {
        $cari = trim((string) $request->query('cari', ''));

        $bukus = Buku::with('kategori')
            ->when($cari !== '', function ($query) use ($cari): void {
                $query->where(function ($query) use ($cari): void {
                    $query->where('kode', 'like', "%{$cari}%")
                        ->orWhere('judul', 'like', "%{$cari}%");
                });
            })
            ->orderBy('id')
            ->get();

        return view('buku.index', compact('bukus', 'cari'));
    }

    public function create(): View
    {
        return view('buku.form', [
            'buku' => new Buku,
            'kategoris' => Kategori::orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'kode' => ['required', 'string', 'max:10', 'unique:bukus,kode'],
            'judul' => ['required', 'string', 'max:255'],
            'kategori_id' => ['required', 'exists:kategoris,id'],
        ], [
            'kode.unique' => 'Kode buku sudah digunakan.',
        ]);

        Buku::create($data);

        return redirect()->route('buku.index')->with('status', 'Buku ditambahkan.');
    }

    public function edit(Buku $buku): View
    {
        return view('buku.form', [
            'buku' => $buku,
            'kategoris' => Kategori::orderBy('nama')->get(),
        ]);
    }

    public function update(Request $request, Buku $buku): RedirectResponse
    {
        $data = $request->validate([
            'kode' => [
                'required',
                'string',
                'max:10',
                Rule::unique('bukus', 'kode')->ignore($buku->id),
            ],
            'judul' => ['required', 'string', 'max:255'],
            'kategori_id' => ['required', 'exists:kategoris,id'],
        ], [
            'kode.unique' => 'Kode buku sudah digunakan.',
        ]);

        $buku->update($data);

        return redirect()->route('buku.index')->with('status', 'Buku diperbarui.');
    }

    public function destroy(Buku $buku): RedirectResponse
    {
        $buku->delete();

        return redirect()->route('buku.index')->with('status', 'Buku dihapus.');
    }
}
