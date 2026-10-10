@php $k = $kategori ?? null; @endphp

<div class="row g-3">
    <div class="col-md-4">
        <label for="kode_kategori" class="form-label">Kode Kategori</label>
        <input type="text" id="kode_kategori" name="kode_kategori"
               class="form-control @error('kode_kategori') is-invalid @enderror"
               value="{{ old('kode_kategori', $k?->kode_kategori) }}">
        @error('kode_kategori') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-8">
        <label for="nama_kategori" class="form-label">Nama Kategori</label>
        <input type="text" id="nama_kategori" name="nama_kategori"
               class="form-control @error('nama_kategori') is-invalid @enderror"
               value="{{ old('nama_kategori', $k?->nama_kategori) }}">
        @error('nama_kategori') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="mt-4">
    <button type="submit" class="btn btn-del">Simpan</button>
    <a href="{{ route('kategori.index') }}" class="btn btn-outline-secondary">Batal</a>
</div>