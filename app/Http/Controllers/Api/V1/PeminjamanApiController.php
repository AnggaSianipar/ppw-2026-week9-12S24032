<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PeminjamanResource;
use App\Models\Buku;
use App\Models\Peminjaman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

// Provider transaksi sirkulasi dengan transaksi ACID dan row locking.
class PeminjamanApiController extends Controller
{
    public function index(Request $request)
    {
        $loans = Peminjaman::with('buku')
            ->when($request->filled('status'), function ($query) use ($request): void {
                $query->whereIn('status', ['dipinjam', 'dikembalikan'])
                    ->where('status', $request->string('status')->toString());
            })
            ->when($request->filled('nim'), function ($query) use ($request): void {
                $query->where('nim_peminjam', $request->string('nim')->toString());
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return PeminjamanResource::collection($loans);
    }

    public function show(Peminjaman $peminjaman): PeminjamanResource
    {
        return new PeminjamanResource($peminjaman->load('buku'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'buku_id' => ['required', 'integer', 'exists:bukus,id'],
            'nim_peminjam' => ['required', 'string', 'max:30'],
            'nama_peminjam' => ['required', 'string', 'max:150'],
            'durasi_hari' => ['required', 'integer', 'min:1', 'max:365'],
        ]);

        $loan = DB::transaction(function () use ($validated): Peminjaman {
            $book = Buku::whereKey($validated['buku_id'])->lockForUpdate()->firstOrFail();

            if ($book->stok < 1) {
                abort(response()->json([
                    'type' => 'https://httpstatuses.com/422',
                    'title' => 'Unprocessable Content',
                    'status' => 422,
                    'detail' => 'Stok buku sedang habis.',
                ], 422, ['Content-Type' => 'application/problem+json']));
            }

            $duplicate = Peminjaman::where('buku_id', $book->id)
                ->where('nim_peminjam', $validated['nim_peminjam'])
                ->where('status', 'dipinjam')
                ->exists();

            if ($duplicate) {
                abort(response()->json([
                    'type' => 'https://httpstatuses.com/409',
                    'title' => 'Conflict',
                    'status' => 409,
                    'detail' => 'Mahasiswa masih meminjam buku yang sama.',
                ], 409, ['Content-Type' => 'application/problem+json']));
            }

            $book->decrement('stok');

            return Peminjaman::create([
                'kode_pinjam' => 'PJ-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4)),
                'buku_id' => $book->id,
                'nim_peminjam' => $validated['nim_peminjam'],
                'nama_peminjam' => $validated['nama_peminjam'],
                'tanggal_pinjam' => today(),
                'tanggal_jatuh_tempo' => today()->addDays($validated['durasi_hari']),
                'status' => 'dipinjam',
            ]);
        });

        return (new PeminjamanResource($loan->load('buku')))
            ->response()
            ->setStatusCode(201)
            ->header('Location', route('api.v1.peminjamans.show', $loan));
    }

    public function return(Peminjaman $peminjaman): PeminjamanResource
    {
        $loan = DB::transaction(function () use ($peminjaman): Peminjaman {
            $lockedLoan = Peminjaman::whereKey($peminjaman->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedLoan->status === 'dikembalikan') {
                abort(response()->json([
                    'type' => 'https://httpstatuses.com/409',
                    'title' => 'Conflict',
                    'status' => 409,
                    'detail' => 'Buku sudah dikembalikan.',
                ], 409, ['Content-Type' => 'application/problem+json']));
            }

            $book = Buku::whereKey($lockedLoan->buku_id)->lockForUpdate()->firstOrFail();
            $lockedLoan->update([
                'status' => 'dikembalikan',
                'tanggal_kembali' => today(),
            ]);
            $book->increment('stok');

            return $lockedLoan->fresh('buku');
        });

        return new PeminjamanResource($loan);
    }
}
