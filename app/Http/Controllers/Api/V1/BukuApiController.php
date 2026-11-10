<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\BukuResource;
use App\Models\Buku;
use App\Traits\ApiResponse;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class BukuApiController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $allowedSorts = [
            'judul', 'penulis', 'isbn', 'stok', 'created_at'
        ];

        $sortBy = in_array($request->query('sort'), $allowedSorts)
            ? $request->query('sort')
            : 'judul';

        $sortDir = strtolower($request->query('dir', 'asc')) === 'desc'
            ? 'desc'
            : 'asc';

        $perPage = min(max((int) $request->query('per_page', 5), 1), 50);

        $paginator = Buku::query()
            ->with('kategori')
            ->withCount('peminjamans')
            ->when($request->query('search'), function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('judul', 'like', "%{$search}%")
                        ->orWhere('penulis', 'like', "%{$search}%")
                        ->orWhere('isbn', 'like', "%{$search}%");
                });
            })
            ->when(
                $request->query('kategori_id'),
                fn ($q, $id) => $q->where('kategori_id', (int) $id)
            )
            ->when(
                $request->boolean('tersedia'),
                fn ($q) => $q->where('stok', '>', 0)
            )
            ->orderBy($sortBy, $sortDir)
            ->paginate($perPage)
            ->withQueryString();

        return $this->successResponse(
            data: BukuResource::collection($paginator->items()),
            message: 'Daftar koleksi buku berhasil diambil.',
            meta: [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total_items' => $paginator->total(),
                'total_pages' => $paginator->lastPage(),
                'links' => [
                    'first' => $paginator->url(1),
                    'prev' => $paginator->previousPageUrl(),
                    'next' => $paginator->nextPageUrl(),
                    'last' => $paginator->url($paginator->lastPage()),
                ],
            ]
        );
    }

    public function show(string $id): JsonResponse
    {
        $buku = Buku::with(['kategori', 'peminjamans'])
            ->withCount('peminjamans')
            ->find($id);

        if (!$buku) {
            return $this->notFoundBuku($id);
        }

        return $this->successResponse(
            data: new BukuResource($buku),
            message: 'Detail buku berhasil ditemukan.'
        );
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'penulis' => 'required|string|max:150',
            'penerbit' => 'required|string|max:150',
            'tahun_terbit' => 'required|integer|min:1900|max:2100',
            'isbn' => 'required|string|max:20|unique:bukus,isbn',
            'kategori_id' => 'required|integer|exists:kategoris,id',
            'stok' => 'required|integer|min:0',
        ]);

        $buku = Buku::create($validated);

        $buku->load('kategori')
            ->loadCount('peminjamans');

        return $this->createdResponse(
            data: new BukuResource($buku),
            locationUrl: url("/api/v1/bukus/{$buku->id}"),
            message: 'Buku baru berhasil dibuat.'
        );
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $buku = Buku::find($id);

        if (!$buku) {
            return $this->notFoundBuku($id);
        }

        $isPut = $request->isMethod('put');

        if ($isPut) {
            $validated = $request->validate([
                'judul' => 'required|string|max:255',
                'penulis' => 'required|string|max:150',
                'penerbit' => 'required|string|max:150',
                'tahun_terbit' => 'required|integer|min:1900|max:2100',
                'isbn' => "required|string|max:20|unique:bukus,isbn,{$buku->id}",
                'kategori_id' => 'required|integer|exists:kategoris,id',
                'stok' => 'required|integer|min:0',
            ]);
        } else {
            $validated = $request->validate([
                'judul' => 'sometimes|required|string|max:255',
                'penulis' => 'sometimes|required|string|max:150',
                'penerbit' => 'sometimes|required|string|max:150',
                'tahun_terbit' => 'sometimes|required|integer|min:1900|max:2100',
                'isbn' => "sometimes|required|string|max:20|unique:bukus,isbn,{$buku->id}",
                'kategori_id' => 'sometimes|required|integer|exists:kategoris,id',
                'stok' => 'sometimes|required|integer|min:0',
            ]);

            if (empty($validated)) {
                return $this->problemResponse(
                    title: 'Bad Request',
                    detail: 'Permintaan PATCH wajib menyertakan minimal satu atribut yang ingin diperbarui.',
                    status: 400,
                    errorSlug: 'empty-patch-payload'
                );
            }
        }

        $buku->update($validated);

        $buku->load('kategori')
            ->loadCount('peminjamans');

        return $this->successResponse(
            data: new BukuResource($buku),
            message: $isPut
                ? 'Seluruh representasi buku berhasil diganti (PUT).'
                : 'Atribut buku berhasil diperbarui sebagian (PATCH).'
        );
    }

    public function destroy(string $id): JsonResponse
    {
        $buku = Buku::find($id);

        if (!$buku) {
            return $this->notFoundBuku($id);
        }

        $pinjamanAktif = $buku->peminjamans()
            ->where('status', 'dipinjam')
            ->count();

        if ($pinjamanAktif > 0) {
            return $this->problemResponse(
                title: 'Conflict - Book Has Active Loans',
                detail: "Buku '{$buku->judul}' tidak dapat dihapus karena sedang dipinjam ({$pinjamanAktif} peminjaman aktif).",
                status: 409,
                errorSlug: 'buku-has-active-loans'
            );
        }

        $buku->delete();

        return $this->noContentResponse();
    }

    public function referensiEksternal(string $id): JsonResponse
    {
        $buku = Buku::find($id);

        if (!$buku) {
            return $this->notFoundBuku($id);
        }

        if (empty($buku->isbn)) {
            return $this->problemResponse(
                title: 'Unprocessable Content',
                detail: 'Buku ini tidak memiliki ISBN sehingga tidak bisa dicari di layanan eksternal.',
                status: 422,
                errorSlug: 'isbn-missing'
            );
        }

        $isbn = preg_replace('/[^0-9Xx]/', '', $buku->isbn);

        $key = "ISBN:{$isbn}";

        try {
            $response = Http::accept('application/json')
                ->withOptions([
                    'force_ip_resolve' => 'v4'
                ])
                ->timeout(8)
                ->get('https://openlibrary.org/api/books', [
                    'bibkeys' => $key,
                    'format' => 'json',
                    'jscmd' => 'data',
                ]);
        } catch (ConnectionException $e) {
            return $this->problemResponse(
                title: 'Gateway Timeout',
                detail: 'Layanan eksternal OpenLibrary tidak merespons dalam 8 detik. Coba lagi nanti.',
                status: 504,
                errorSlug: 'external-timeout'
            );
        }

        if ($response->failed()) {
            return $this->problemResponse(
                title: 'Bad Gateway',
                detail: 'Layanan eksternal OpenLibrary sedang bermasalah.',
                status: 502,
                errorSlug: 'external-service-error'
            );
        }

        $info = $response->json($key);

        if (empty($info)) {
            return $this->problemResponse(
                title: 'External Resource Not Found',
                detail: "ISBN '{$buku->isbn}' tidak ditemukan di OpenLibrary.",
                status: 404,
                errorSlug: 'external-book-not-found'
            );
        }

        return $this->successResponse(
            data: [
                'buku_lokal' => [
                    'id' => $buku->id,
                    'judul' => $buku->judul,
                    'isbn' => $buku->isbn,
                ],
                'referensi_eksternal' => [
                    'sumber' => 'OpenLibrary',
                    'judul' => $info['title'] ?? null,
                    'jumlah_halaman' => $info['number_of_pages'] ?? null,
                    'penerbit' => collect($info['publishers'] ?? [])
                        ->pluck('name')
                        ->all(),
                    'tanggal_terbit' => $info['publish_date'] ?? null,
                    'penulis' => collect($info['authors'] ?? [])
                        ->pluck('name')
                        ->all(),
                    'cover' => $info['cover']['medium'] ?? null,
                    'url_pratinjau' => $info['url'] ?? null,
                ],
            ],
            message: 'Metadata eksternal buku berhasil diambil dari OpenLibrary.'
        );
    }

    private function notFoundBuku(string $id): JsonResponse
    {
        return $this->problemResponse(
            title: 'Resource Not Found',
            detail: "Buku dengan ID '{$id}' tidak ditemukan di basis data.",
            status: 404,
            errorSlug: 'buku-not-found'
        );
    }
}
