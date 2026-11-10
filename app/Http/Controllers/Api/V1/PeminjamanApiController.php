<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PeminjamanResource;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PeminjamanApiController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->query('per_page', 5), 1), 50);

        $paginator = Peminjaman::query()
            ->with('buku')
            ->when(in_array($request->query('status'), ['dipinjam', 'dikembalikan']),
                fn ($q) => $q->where('status', $request->query('status')))
            ->when($request->query('nim'),
                fn ($q, $nim) => $q->where('nim_peminjam', strtoupper(trim($nim))))
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        return $this->successResponse(
            data: PeminjamanResource::collection($paginator->items()),
            message: 'Daftar transaksi peminjaman berhasil diambil.',
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
        $p = Peminjaman::with('buku')->find($id);
        if (!$p) {
            return $this->notFound($id);
        }

        return $this->successResponse(new PeminjamanResource($p), 'Detail peminjaman berhasil ditemukan.');
    }

    /**
     * POST /api/v1/peminjamans  (ACID + lockForUpdate)
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'buku_id'       => 'required|integer|exists:bukus,id',
            'nim_peminjam'  => 'required|string|max:15',
            'nama_peminjam' => 'required|string|max:150',
            'durasi_hari'   => 'required|integer|min:1|max:30',
        ]);

        $nim = strtoupper(trim($validated['nim_peminjam']));

        return DB::transaction(function () use ($validated, $nim) {
            // Kunci baris buku agar stok tidak bisa diperebutkan dua request sekaligus
            $buku = Buku::where('id', $validated['buku_id'])->lockForUpdate()->first();

            if (!$buku) {
                return $this->problemResponse('Resource Not Found', 'Buku tidak ditemukan.', 404, 'book-not-found');
            }

            $sudahMeminjam = Peminjaman::where('buku_id', $buku->id)
                ->where('nim_peminjam', $nim)
                ->where('status', 'dipinjam')
                ->exists();

            if ($sudahMeminjam) {
                return $this->problemResponse(
                    'Duplicate Loan Conflict',
                    "NIM '{$nim}' masih meminjam buku '{$buku->judul}' dan belum mengembalikannya.",
                    409,
                    'duplicate-loan'
                );
            }

            if ($buku->stok < 1) {
                return $this->problemResponse(
                    'Book Out Of Stock',
                    "Stok buku sedang habis untuk '{$buku->judul}'.",
                    422,
                    'stock-exhausted'
                );
            }

            $buku->decrement('stok');

            $peminjaman = Peminjaman::create([
                'kode_pinjam'         => 'PJM-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5)),
                'buku_id'             => $buku->id,
                'nim_peminjam'        => $nim,
                'nama_peminjam'       => $validated['nama_peminjam'],
                'tanggal_pinjam'      => now()->toDateString(),
                'tanggal_jatuh_tempo' => now()->addDays((int) $validated['durasi_hari'])->toDateString(),
                'status'              => 'dipinjam',
            ]);
            $peminjaman->load('buku');

            return $this->createdResponse(
                data: new PeminjamanResource($peminjaman),
                locationUrl: url("/api/v1/peminjamans/{$peminjaman->id}"),
                message: "Peminjaman berhasil dicatat. Sisa stok buku: {$buku->stok}."
            );
        });
    }

    /**
     * PATCH /api/v1/peminjamans/{id}/kembalikan  (ACID + lockForUpdate)
     */
    public function kembalikan(string $id): JsonResponse
    {
        return DB::transaction(function () use ($id) {
            $peminjaman = Peminjaman::where('id', $id)->lockForUpdate()->first();

            if (!$peminjaman) {
                return $this->notFound($id);
            }

            if ($peminjaman->status === 'dikembalikan') {
                return $this->problemResponse(
                    'Already Returned Conflict',
                    'Buku pada transaksi ini sudah dikembalikan sebelumnya.',
                    409,
                    'already-returned'
                );
            }

            $buku = Buku::where('id', $peminjaman->buku_id)->lockForUpdate()->first();

            $peminjaman->update([
                'status'          => 'dikembalikan',
                'tanggal_kembali' => now()->toDateString(),
            ]);

            if ($buku) {
                $buku->increment('stok');
            }

            $peminjaman->load('buku');

            return $this->successResponse(
                new PeminjamanResource($peminjaman),
                'Buku berhasil dikembalikan dan stok diperbarui.'
            );
        });
    }

    private function notFound(string $id): JsonResponse
    {
        return $this->problemResponse(
            'Resource Not Found',
            "Data peminjaman dengan ID '{$id}' tidak ditemukan.",
            404,
            'loan-not-found'
        );
    }
}