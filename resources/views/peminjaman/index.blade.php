<x-layout title="Riwayat Peminjaman">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0 text-del">Riwayat Peminjaman</h1>
        <a href="{{ route('peminjaman.create') }}" class="btn btn-del">+ Catat Peminjaman</a>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Kode</th>
                        <th>Buku</th>
                        <th>Peminjam</th>
                        <th>Tgl Pinjam</th>
                        <th>Jatuh Tempo</th>
                        <th>Tgl Kembali</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($peminjamans as $p)
                        <tr>
                            <td>{{ $p->kode_pinjam }}</td>
                            <td>{{ $p->buku->judul }}</td>
                            <td>{{ $p->nama_peminjam }}<br><small class="text-muted">{{ $p->nim_peminjam }}</small></td>
                            <td>{{ $p->tanggal_pinjam->format('d M Y') }}</td>
                            <td>{{ $p->tanggal_jatuh_tempo->format('d M Y') }}</td>
                            <td>{{ $p->tanggal_kembali ? $p->tanggal_kembali->format('d M Y') : '-' }}</td>
                            <td>
                                @if($p->status === 'dipinjam')
                                    <span class="badge bg-warning text-dark">Dipinjam</span>
                                @else
                                    <span class="badge bg-success">Dikembalikan</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($p->status === 'dipinjam')
                                    <form action="{{ route('peminjaman.kembali', $p) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Tandai buku ini sudah dikembalikan?')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-outline-success">Kembalikan</button>
                                    </form>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Belum ada data peminjaman.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $peminjamans->links() }}
    </div>
</x-layout>