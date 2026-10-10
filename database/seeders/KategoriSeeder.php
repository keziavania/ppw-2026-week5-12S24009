<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Kategori;

class KategoriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
{
    $data = [
        ['TIF', 'Teknologi Informasi'],
        ['SNS', 'Sains'],
        ['MTK', 'Matematika'],
        ['SST', 'Sastra'],
        ['BIS', 'Bisnis dan Manajemen'],
    ];

    foreach ($data as [$kode, $nama]) {
        Kategori::create(['kode_kategori' => $kode, 'nama_kategori' => $nama]);
    }
}
}
