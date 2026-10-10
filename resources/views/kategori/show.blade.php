<x-layout title="Detail Kategori">
    <h1 class="h4 mb-3 text-del">Detail Kategori</h1>

    <div class="card card-body shadow-sm mb-4">
        <dl class="row mb-0">
            <dt class="col-sm-3">Kode</dt><dd class="col-sm-9">{{ $kategori->kode_kategori }}</dd>
            <dt class="col-sm-3">Nama</dt><dd class="col-sm-9">{{ $kategori->nama_kategori }}</dd>
        </dl>
    </div>

    <h2 class="h5 text-del">Buku dalam kategori ini</h2>
    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ISBN</th>
                        <th>Judul</th>
                        <th>Penulis</th>
                        <th>Stok</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bukus as $buku)
                        <tr>
                            <td>{{ $buku->isbn }}</td>
                            <td><a href="{{ route('buku.show', $buku) }}">{{ $buku->judul }}</a></td>
                            <td>{{ $buku->penulis }}</td>
                            <td>{{ $buku->stok }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">Belum ada buku di kategori ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $bukus->links() }}
    </div>

    <div class="mt-3">
        <a href="{{ route('kategori.edit', $kategori) }}" class="btn btn-del">Edit</a>
        <a href="{{ route('kategori.index') }}" class="btn btn-outline-secondary">Kembali</a>
    </div>
</x-layout>