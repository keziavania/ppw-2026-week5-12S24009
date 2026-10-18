<?php

namespace Database\Seeders;

use App\Models\Buku;
use App\Models\Peminjaman;
use Illuminate\Database\Seeder;

class PeminjamanSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil buku yang stoknya cukup (sampel stok 0 dan 1 tidak disentuh)
        $buku = Buku::where('stok', '>', 1)->inRandomOrder()->limit(3)->get();

        if ($buku->count() < 3) {
            return;
        }

        // 1. Sudah dikembalikan (stok kembali seperti semula)
        Peminjaman::create([
            'kode_pinjam' => 'PJM-SAMPEL-001',
            'buku_id' => $buku[0]->id,
            'nim_peminjam' => '12S24001',
            'nama_peminjam' => 'Mahasiswa Uji Satu',
            'tanggal_pinjam' => now()->subDays(14)->toDateString(),
            'tanggal_jatuh_tempo' => now()->subDays(7)->toDateString(),
            'tanggal_kembali' => now()->subDays(9)->toDateString(),
            'status' => 'dikembalikan',
        ]);

        // 2. Sedang dipinjam (stok berkurang 1)
        Peminjaman::create([
            'kode_pinjam' => 'PJM-SAMPEL-002',
            'buku_id' => $buku[1]->id,
            'nim_peminjam' => '12S24002',
            'nama_peminjam' => 'Mahasiswa Uji Dua',
            'tanggal_pinjam' => now()->subDays(2)->toDateString(),
            'tanggal_jatuh_tempo' => now()->addDays(5)->toDateString(),
            'status' => 'dipinjam',
        ]);
        $buku[1]->decrement('stok');

        // 3. Sedang dipinjam dan sudah lewat jatuh tempo (stok berkurang 1)
        Peminjaman::create([
            'kode_pinjam' => 'PJM-SAMPEL-003',
            'buku_id' => $buku[2]->id,
            'nim_peminjam' => '12S24003',
            'nama_peminjam' => 'Mahasiswa Uji Tiga',
            'tanggal_pinjam' => now()->subDays(12)->toDateString(),
            'tanggal_jatuh_tempo' => now()->subDays(5)->toDateString(),
            'status' => 'dipinjam',
        ]);
        $buku[2]->decrement('stok');
    }
}