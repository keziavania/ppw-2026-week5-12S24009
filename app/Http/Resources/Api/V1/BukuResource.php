<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BukuResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $links = [
            'self'                => ['href' => url("/api/v1/bukus/{$this->id}"), 'method' => 'GET'],
            'update_penuh'        => ['href' => url("/api/v1/bukus/{$this->id}"), 'method' => 'PUT'],
            'update_sebagian'     => ['href' => url("/api/v1/bukus/{$this->id}"), 'method' => 'PATCH'],
            'hapus'               => ['href' => url("/api/v1/bukus/{$this->id}"), 'method' => 'DELETE'],
            'referensi_eksternal' => ['href' => url("/api/v1/bukus/{$this->id}/referensi-eksternal"), 'method' => 'GET'],
        ];

        if ($this->stok > 0) {
            $links['pinjam_buku'] = ['href' => url('/api/v1/peminjamans'), 'method' => 'POST'];
        }

        return [
            'id'           => $this->id,
            'judul'        => $this->judul,
            'penulis'      => $this->penulis,
            'penerbit'     => $this->penerbit,
            'tahun_terbit' => $this->tahun_terbit,
            'isbn'         => $this->isbn,
            'stok'         => (int) $this->stok,
            'status_label' => $this->stok > 0 ? 'TERSEDIA' : 'HABIS',
            'kategori' => $this->whenLoaded('kategori', fn () => [
                'id'   => $this->kategori->id,
                'nama' => $this->kategori->nama_kategori,
                ]),
            'jumlah_peminjaman'  => $this->whenCounted('peminjamans'),
            'riwayat_peminjaman' => $this->whenLoaded('peminjamans', fn () =>
                $this->peminjamans->map(fn ($p) => [
                    'id'              => $p->id,
                    'nim_peminjam'    => $p->nim_peminjam,
                    'nama_peminjam'   => $p->nama_peminjam,
                    'status'          => $p->status,
                    'tanggal_kembali' => $p->tanggal_kembali,
                ])
            ),
            '_links' => $links,
        ];
    }
}