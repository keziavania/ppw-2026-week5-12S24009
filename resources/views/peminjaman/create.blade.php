<x-layout title="Catat Peminjaman">
    <h1 class="h4 mb-3 text-del">Catat Peminjaman Buku</h1>

    <form action="{{ route('peminjaman.store') }}" method="POST" class="card card-body shadow-sm">
        @csrf
        <div class="row g-3">
            <div class="col-12">
                <label for="buku_id" class="form-label">Buku</label>
                <select id="buku_id" name="buku_id" class="form-select @error('buku_id') is-invalid @enderror">
                    <option value="">-- Pilih buku --</option>
                    @foreach($bukus as $buku)
                        <option value="{{ $buku->id }}" @selected(old('buku_id', $bukuTerpilih) == $buku->id)>
                            {{ $buku->judul }} ({{ $buku->isbn }}) — stok: {{ $buku->stok }}
                        </option>
                    @endforeach
                </select>
                @error('buku_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
                <label for="nim_peminjam" class="form-label">NIM Peminjam</label>
                <input type="text" id="nim_peminjam" name="nim_peminjam"
                       class="form-control @error('nim_peminjam') is-invalid @enderror"
                       value="{{ old('nim_peminjam') }}">
                @error('nim_peminjam') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-5">
                <label for="nama_peminjam" class="form-label">Nama Peminjam</label>
                <input type="text" id="nama_peminjam" name="nama_peminjam"
                       class="form-control @error('nama_peminjam') is-invalid @enderror"
                       value="{{ old('nama_peminjam') }}">
                @error('nama_peminjam') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-3">
                <label for="lama_pinjam" class="form-label">Lama Pinjam (hari)</label>
                <input type="number" id="lama_pinjam" name="lama_pinjam" min="1" max="30"
                       class="form-control @error('lama_pinjam') is-invalid @enderror"
                       value="{{ old('lama_pinjam', 7) }}">
                @error('lama_pinjam') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-del">Simpan Peminjaman</button>
            <a href="{{ route('peminjaman.index') }}" class="btn btn-outline-secondary">Batal</a>
        </div>
    </form>
</x-layout>