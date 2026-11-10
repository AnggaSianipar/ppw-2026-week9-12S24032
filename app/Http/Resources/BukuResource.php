<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BukuResource extends JsonResource
{
    // Resource mengubah model buku menjadi JSON konsisten sekaligus HATEOAS.
    public function toArray(Request $request): array
    {
        $links = [
            'self' => [
                'href' => route('api.v1.bukus.show', $this->id),
                'method' => 'GET',
            ],
        ];

        if ($this->stok > 0) {
            $links['pinjam_buku'] = [
                'href' => route('api.v1.peminjamans.store'),
                'method' => 'POST',
            ];
        }

        return [
            'id' => $this->id,
            'isbn' => $this->isbn,
            'judul' => $this->judul,
            'penulis' => $this->penulis,
            'penerbit' => $this->penerbit,
            'tahun_terbit' => $this->tahun_terbit,
            'stok' => $this->stok,
            'sinopsis' => $this->sinopsis,
            'jumlah_peminjaman' => $this->when(isset($this->peminjamans_count), $this->peminjamans_count),
            'kategori' => new KategoriResource($this->whenLoaded('kategori')),
            'peminjamans' => PeminjamanResource::collection(
                $this->whenLoaded('peminjamans')
            ),
            '_links' => $links,
        ];
    }
}