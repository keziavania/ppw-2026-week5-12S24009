<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class PeminjamanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $fmt = fn ($d) => $d ? Carbon::parse($d)->toDateString() : null;

        $links = [
            'buku' => ['href' => url("/api/v1/bukus/{$this->buku_id}"), 'method' => 'GET'],
        ];

        // HATEOAS dinamis: link kembalikan hanya muncul jika masih dipinjam
        if ($this->status === 'dipinjam') {
            $links['kembalikan_buku'] = [
                'href'   => url("/api/v1/peminjamans/{$this->id}/kembalikan"),
                'method' => 'PATCH',
            ];
        }

        return [
            'id'                  => $this->id,
            'kode_pinjam'         => $this->kode_pinjam,
            'nim_peminjam'        => $this->nim_peminjam,
            'nama_peminjam'       => $this->nama_peminjam,
            'tanggal_pinjam'      => $fmt($this->tanggal_pinjam),
            'tanggal_jatuh_tempo' => $fmt($this->tanggal_jatuh_tempo),
            'tanggal_kembali'     => $fmt($this->tanggal_kembali),
            'status'              => $this->status,
            'buku'                => $this->whenLoaded('buku', fn () => [
                'id'    => $this->buku->id,
                'judul' => $this->buku->judul,
                'isbn'  => $this->buku->isbn,
                'stok'  => (int) $this->buku->stok,
            ]),
            '_links' => $links,
        ];
    }
}