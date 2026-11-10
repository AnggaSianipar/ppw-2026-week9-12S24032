<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Buku;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

// Consumer OpenLibrary: mengambil metadata tambahan tanpa menggagalkan API utama.
class ExternalBookController extends Controller
{
    public function show(Buku $buku): JsonResponse
    {
        try {
            $response = Http::timeout(8)->acceptJson()->get(
                'https://openlibrary.org/api/books',
                [
                    'bibkeys' => 'ISBN:' . $buku->isbn,
                    'format' => 'json',
                    'jscmd' => 'data',
                ]
            );
        } catch (ConnectionException) {
            return response()->json([
                'data' => null,
                'message' => 'Layanan OpenLibrary sedang tidak dapat dihubungi.',
            ]);
        }

        $key = 'ISBN:' . $buku->isbn;
        if ($response->failed() || ! is_array($response->json($key))) {
            return response()->json([
                'data' => null,
                'message' => 'Metadata eksternal tidak ditemukan.',
            ]);
        }

        return response()->json([
            'data' => $response->json($key),
            'source' => 'OpenLibrary',
        ]);
    }
}
