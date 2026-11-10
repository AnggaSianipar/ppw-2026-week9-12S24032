<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BukuResource;
use App\Models\Buku;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

// Provider katalog buku: pencarian publik dan operasi CRUD ber-token.
class BukuApiController extends Controller
{
    public function index(Request $request)
    {
        $allowedSorts = ['judul', 'penulis', 'tahun_terbit', 'stok', 'created_at'];
        $sort = $request->string('sort')->toString();
        $direction = str_starts_with($sort, '-') ? 'desc' : 'asc';
        $column = ltrim($sort, '-');

        if (! in_array($column, $allowedSorts, true)) {
            $column = 'created_at';
            $direction = 'desc';
        }

        $books = Buku::query()
            ->with('kategori')
            ->withCount('peminjamans')
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = $request->string('search')->toString();
                $query->where(function ($query) use ($search): void {
                    $query->where('judul', 'like', "%{$search}%")
                        ->orWhere('penulis', 'like', "%{$search}%")
                        ->orWhere('isbn', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('kategori_id'), function ($query) use ($request): void {
                $query->where('kategori_id', $request->integer('kategori_id'));
            })
            ->when($request->input('tersedia') === '1', function ($query): void {
                $query->where('stok', '>', 0);
            })
            ->orderBy($column, $direction)
            ->paginate(10)
            ->withQueryString();

        return BukuResource::collection($books);
    }

    public function show(Buku $buku): BukuResource
    {
        $buku->load([
            'kategori',
            'peminjamans' => fn ($query) => $query->latest(),
        ]);

        return new BukuResource($buku);
    }

    public function store(Request $request): JsonResponse
    {
        $buku = Buku::create($this->validatedData($request));

        return (new BukuResource($buku->load('kategori')))
            ->response()
            ->setStatusCode(201)
            ->header('Location', route('api.v1.bukus.show', $buku));
    }

    public function update(Request $request, Buku $buku): BukuResource
    {
        $buku->update($this->validatedData($request, $buku, false));

        return new BukuResource($buku->fresh('kategori'));
    }

    public function partialUpdate(Request $request, Buku $buku): BukuResource
    {
        $buku->update($this->validatedData($request, $buku, true));

        return new BukuResource($buku->fresh('kategori'));
    }

    public function destroy(Request $request, Buku $buku): JsonResponse
    {
        if ($buku->peminjamans()->where('status', 'dipinjam')->exists()) {
            return response()->json([
                'type' => 'https://httpstatuses.com/409',
                'title' => 'Conflict',
                'status' => 409,
                'detail' => 'Buku masih memiliki peminjaman aktif.',
                'instance' => $request->getRequestUri(),
            ], 409, ['Content-Type' => 'application/problem+json']);
        }

        $buku->delete();

        return response()->json(null, 204);
    }

    private function validatedData(Request $request, ?Buku $buku = null, bool $partial = false): array
    {
        $prefix = $partial ? 'sometimes|required' : 'required';
        $isbnRule = Rule::unique('bukus', 'isbn');

        if ($buku !== null) {
            $isbnRule->ignore($buku->id);
        }

        return $request->validate([
            'isbn' => [$prefix, 'string', 'max:20', $isbnRule],
            'judul' => [$prefix, 'string', 'min:5', 'max:200'],
            'penulis' => [$prefix, 'string', 'max:150'],
            'penerbit' => [$prefix, 'string', 'max:150'],
            'tahun_terbit' => [$prefix, 'integer', 'between:1000,2100'],
            'kategori_id' => [$prefix, 'integer', 'exists:kategoris,id'],
            'stok' => [$prefix, 'integer', 'min:0'],
            'sinopsis' => [$partial ? 'sometimes' : 'nullable', 'string'],
        ]);
    }
}