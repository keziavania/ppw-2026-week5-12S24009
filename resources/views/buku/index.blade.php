<x-layout title="Daftar Buku">
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card shadow-sm h-100"><div class="card-body">
                <div class="text-muted small">Total Judul Buku</div>
                <div class="h3 mb-0 text-del">{{ $stats['total_judul'] }}</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm h-100"><div class="card-body">
                <div class="text-muted small">Total Stok Tersedia</div>
                <div class="h3 mb-0 text-del">{{ $stats['total_stok'] }}</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm h-100"><div class="card-body">
                <div class="text-muted small">Sedang Dipinjam</div>
                <div class="h3 mb-0 text-del">{{ $stats['sedang_dipinjam'] }}</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm h-100"><div class="card-body">
                <div class="text-muted small">Total Dikembalikan</div>
                <div class="h3 mb-0 text-del">{{ $stats['dikembalikan'] }}</div>
            </div></div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0 text-del">Daftar Buku</h1>
        <a href="{{ route('buku.create') }}" class="btn btn-del">+ Tambah Buku</a>
    </div>

    <form action="{{ route('buku.index') }}" method="GET" class="row g-2 mb-3">
        <div class="col-md-6">
            <input type="text" name="search" class="form-control"
                   placeholder="Cari judul, penulis, atau ISBN..." value="{{ request('search') }}">
        </div>
        <div class="col-md-4">
            <select name="kategori_id" class="form-select">
                <option value="">Semua kategori</option>
                @foreach($kategoris as $kategori)
                    <option value="{{ $kategori->id }}" @selected(request('kategori_id') == $kategori->id)>
                        {{ $kategori->nama_kategori }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-del flex-fill">Cari</button>
            <a href="{{ route('buku.index') }}" class="btn btn-outline-secondary">Reset</a>
        </div>
    </form>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>ISBN</th>
                        <th>Judul</th>
                        <th>Penulis</th>
                        <th>Kategori</th>
                        <th>Stok</th>
                        <th>Dipinjam</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bukus as $buku)
                        <tr>
                            <td>{{ $bukus->firstItem() + $loop->index }}</td>
                            <td>{{ $buku->isbn }}</td>
                            <td>{{ $buku->judul }}</td>
                            <td>{{ $buku->penulis }}</td>
                            <td><span class="badge bg-secondary">{{ $buku->kategori->nama_kategori }}</span></td>
                            <td>{{ $buku->stok }}</td>
                            <td>{{ $buku->peminjamans_count }}x</td>
                            <td class="text-end">
                                <a href="{{ route('peminjaman.create', ['buku_id' => $buku->id]) }}" class="btn btn-sm btn-outline-success">Pinjam</a>
                                <a href="{{ route('buku.show', $buku) }}" class="btn btn-sm btn-outline-secondary">Lihat</a>
                                <a href="{{ route('buku.edit', $buku) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form action="{{ route('buku.destroy', $buku) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Hapus buku ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                {{ request()->hasAny(['search', 'kategori_id']) ? 'Tidak ada buku yang cocok dengan pencarian.' : 'Belum ada data buku.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $bukus->links() }}
    </div>
</x-layout>