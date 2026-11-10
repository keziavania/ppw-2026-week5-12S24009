<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\BukuResource;
use App\Models\Buku;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BukuApiController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $allowedSorts = ['judul', 'penulis', 'stok', 'created_at'];
        $sortBy  = in_array($request->query('sort'), $allowedSorts) ? $request->query('sort') : 'judul';
        $sortDir = strtolower($request->query('dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        $perPage = min(max((int) $request->query('per_page', 5), 1), 50);

        $paginator = Buku::query()
            ->with('kategori')
            ->withCount('peminjamans')
            ->when($request->query('search'), function ($q, $s) {
                $q->where(fn ($sub) => $sub
                    ->where('judul', 'like', "%{$s}%")
                    ->orWhere('penulis', 'like', "%{$s}%")
                    ->orWhere('isbn', 'like', "%{$s}%"));
            })
            ->when($request->query('kategori_id'), fn ($q, $id) => $q->where('kategori_id', (int) $id))
            ->when($request->boolean('tersedia'), fn ($q) => $q->where('stok', '>', 0))
            ->orderBy($sortBy, $sortDir)
            ->paginate($perPage)
            ->withQueryString();

        return $this->successResponse(
            data: BukuResource::collection($paginator->items()),
            message: 'Daftar buku berhasil diambil.',
            meta: [
                'current_page' => $paginator->currentPage(),
                'per_page'     => $paginator->perPage(),
                'total_items'  => $paginator->total(),
                'total_pages'  => $paginator->lastPage(),
                'links' => [
                    'first' => $paginator->url(1),
                    'prev'  => $paginator->previousPageUrl(),
                    'next'  => $paginator->nextPageUrl(),
                    'last'  => $paginator->url($paginator->lastPage()),
                ],
            ]
        );
    }

    public function show(string $id): JsonResponse
    {
        $buku = Buku::with(['kategori', 'peminjamans'])->withCount('peminjamans')->find($id);

        if (!$buku) {
            return $this->notFound($id);
        }

        return $this->successResponse(new BukuResource($buku), 'Detail buku berhasil ditemukan.');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'judul'        => 'required|string|max:200',
            'penulis'      => 'required|string|max:150',
            'penerbit'     => 'required|string|max:150',
            'tahun_terbit' => 'required|integer|min:1900|max:2100',
            'isbn'         => 'required|string|max:20|unique:bukus,isbn',
            'stok'         => 'required|integer|min:0',
            'kategori_id'  => 'required|integer|exists:kategoris,id',
        ]);

        $buku = Buku::create($validated);
        $buku->load('kategori')->loadCount('peminjamans');

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
            return $this->notFound($id);
        }

        $isPut = $request->isMethod('put');
        $req   = $isPut ? 'required' : 'sometimes|required';

        $validated = $request->validate([
            'judul'        => "{$req}|string|max:200",
            'penulis'      => "{$req}|string|max:150",
            'penerbit'     => "{$req}|string|max:150",
            'tahun_terbit' => "{$req}|integer|min:1900|max:2100",
            'isbn'         => [$isPut ? 'required' : 'sometimes', 'string', 'max:20', Rule::unique('bukus', 'isbn')->ignore($buku->id)],
            'stok'         => "{$req}|integer|min:0",
            'kategori_id'  => "{$req}|integer|exists:kategoris,id",
        ]);

        if (!$isPut && empty($validated)) {
            return $this->problemResponse(
                'Bad Request',
                'Permintaan PATCH wajib menyertakan minimal satu atribut.',
                400,
                'empty-patch-payload'
            );
        }

        $buku->update($validated);
        $buku->load('kategori')->loadCount('peminjamans');

        return $this->successResponse(
            new BukuResource($buku),
            $isPut ? 'Seluruh data buku berhasil diganti (PUT).' : 'Data buku berhasil diperbarui sebagian (PATCH).'
        );
    }

    public function destroy(string $id): JsonResponse
    {
        $buku = Buku::find($id);
        if (!$buku) {
            return $this->notFound($id);
        }

        if ($buku->peminjamans()->where('status', 'dipinjam')->exists()) {
            return $this->problemResponse(
                'Conflict — Book Has Active Loans',
                "Buku '{$buku->judul}' tidak dapat dihapus karena sedang dipinjam.",
                409,
                'book-has-active-loans'
            );
        }

        $buku->delete();
        return $this->noContentResponse();
    }

    private function notFound(string $id): JsonResponse
    {
        return $this->problemResponse(
            'Resource Not Found',
            "Buku dengan ID '{$id}' tidak ditemukan.",
            404,
            'book-not-found'
        );
    }
}