<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Buku;
use App\Models\Kategori;

class BukuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
{
    $kategoris = Kategori::all();

    // 18 buku acak + 2 buku sampel = 20 buku (sesuai spesifikasi Minggu 5)
    Buku::factory(18)->recycle($kategoris)->create();

    // Sampel khusus untuk menguji transaksi
    Buku::factory()->recycle($kategoris)->create([
        'isbn' => '9786020000001',
        'judul' => 'Sampel Uji Stok Nol',
        'stok' => 0,
    ]);

    Buku::factory()->recycle($kategoris)->create([
        'isbn' => '9786020000002',
        'judul' => 'Sampel Uji Stok Satu',
        'stok' => 1,
    ]);
}
}
