<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Buku extends Model
{
    use HasFactory;

    protected $fillable = ['judul', 'penulis', 'penerbit', 'tahun_terbit', 'isbn', 'stok', 'kategori_id'];

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class);
    }

    public function peminjamans(): HasMany
    {
    return $this->hasMany(Peminjaman::class);
    }
}