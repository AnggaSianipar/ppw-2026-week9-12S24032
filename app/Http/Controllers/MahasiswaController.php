<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use App\Models\Peminjaman;
use App\Models\ProgramStudi;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MahasiswaController extends Controller
{
    public function index()
    {
        $mahasiswas = Mahasiswa::with('programStudi')->latest()->paginate(10);
        $peminjamanCounts = Peminjaman::query()
            ->selectRaw('nim_peminjam, count(*) as total')
            ->groupBy('nim_peminjam')
            ->pluck('total', 'nim_peminjam');

        return view('mahasiswa.index', compact('mahasiswas', 'peminjamanCounts'));
    }

    public function peminjaman(Mahasiswa $mahasiswa): View
    {
        $peminjamans = Peminjaman::with('buku')
            ->where('nim_peminjam', $mahasiswa->nim)
            ->latest()
            ->paginate(10);

        return view('mahasiswa.peminjaman', compact('mahasiswa', 'peminjamans'));
    }

    public function create()
    {
        $programStudis = ProgramStudi::all();
        return view('mahasiswa.create', compact('programStudis'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nim'              => 'required|digits:8|unique:mahasiswas,nim',
            'nama'             => 'required|string|min:3|max:150',
            'email'            => 'required|email|unique:mahasiswas,email',
            'program_studi_id' => 'required|exists:program_studis,id',
            'ipk'              => 'required|numeric|between:0.00,4.00',
        ]);

        Mahasiswa::create($validated);

        return redirect()->route('mahasiswa.index')->with('success', 'Mahasiswa berhasil ditambahkan!');
    }
}