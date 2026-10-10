<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Kategori;

class BukuFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
{
    $awalan = fake()->randomElement(['Pengantar', 'Dasar-Dasar', 'Panduan Praktis', 'Konsep Modern', 'Analisis']);
    $topik = fake()->randomElement(['Basis Data', 'Pemrograman Web', 'Jaringan Komputer', 'Statistika', 'Manajemen Proyek', 'Kecerdasan Buatan', 'Sistem Informasi']);

    return [
        'isbn' => fake()->unique()->isbn13(),
        'judul' => $awalan . ' ' . $topik,
        'penulis' => fake()->name(),
        'penerbit' => fake()->company(),
        'tahun_terbit' => fake()->numberBetween(2005, 2025),
        'kategori_id' => Kategori::factory(),
        'stok' => fake()->numberBetween(0, 25),
        'sinopsis' => fake()->paragraph(3),
    ];
}
}
