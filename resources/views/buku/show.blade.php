<x-layout title="Detail Buku">
    <h1 class="h4 mb-3 text-del">Detail Buku</h1>
    <div class="card card-body shadow-sm">
        <dl class="row mb-0">
            <dt class="col-sm-3">ISBN</dt><dd class="col-sm-9">{{ $buku->isbn }}</dd>
            <dt class="col-sm-3">Judul</dt><dd class="col-sm-9">{{ $buku->judul }}</dd>
            <dt class="col-sm-3">Penulis</dt><dd class="col-sm-9">{{ $buku->penulis }}</dd>
            <dt class="col-sm-3">Penerbit</dt><dd class="col-sm-9">{{ $buku->penerbit }}</dd>
            <dt class="col-sm-3">Tahun Terbit</dt><dd class="col-sm-9">{{ $buku->tahun_terbit }}</dd>
            <dt class="col-sm-3">Kategori</dt><dd class="col-sm-9">{{ $buku->kategori->nama_kategori }}</dd>
            <dt class="col-sm-3">Stok</dt><dd class="col-sm-9">{{ $buku->stok }}</dd>
            <dt class="col-sm-3">Sinopsis</dt><dd class="col-sm-9">{{ $buku->sinopsis ?: '-' }}</dd>
        </dl>
    </div>
    <div class="mt-3">
        <a href="{{ route('buku.edit', $buku) }}" class="btn btn-del">Edit</a>
        <a href="{{ route('buku.index') }}" class="btn btn-outline-secondary">Kembali</a>
    </div>
</x-layout>