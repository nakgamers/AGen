<?php

namespace App\Http\Controllers;

use App\Models\RmManager;
use Illuminate\Http\Request;

class RmManagerController extends Controller
{
    /** Daftar RM Manager (nama & nomor telepon). */
    public function index(Request $request)
    {
        $query = RmManager::orderBy('name');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $rmManagers = $query->paginate(20)->withQueryString();

        return view('rm-managers.index', compact('rmManagers'));
    }

        public function create()
    {
        return view('rm-managers.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:30'],
        ]);

        $rmManager = RmManager::create($data);

        return redirect()->route('rm-managers.index')
            ->with('success', 'RM ' . $rmManager->name . ' berhasil ditambahkan.');
    }

    public function edit(RmManager $rmManager)
    {
        return view('rm-managers.edit', compact('rmManager'));
    }

    public function update(Request $request, RmManager $rmManager)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:30'],
        ]);

        $rmManager->update($data);

        return redirect()->route('rm-managers.index')
            ->with('success', 'Data RM ' . $rmManager->name . ' berhasil diperbarui.');
    }

    /** Hapus RM (soft delete): data disembunyikan, riwayat rekening tetap utuh. */
    public function destroy(RmManager $rmManager)
    {
        $nama = $rmManager->name;

        $rmManager->delete();

        return redirect()->back()
            ->with('success', 'Data RM ' . $nama . ' berhasil dihapus.');
    }
}