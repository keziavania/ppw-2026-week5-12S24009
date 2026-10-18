@props(['title' => 'SIPUS-Del'])
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} — SIPUS-Del | Institut Teknologi Del</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .navbar-del { background-color: #4C1D95; }
        .btn-del { background-color: #4C1D95; color: #ffffff; }
        .btn-del:hover { background-color: #3B0764; color: #ffffff; }
        .text-del { color: #4C1D95; }
        .pagination .page-link { color: #4C1D95; }
        .pagination .active .page-link { background-color: #4C1D95; border-color: #4C1D95; color: #fff; }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand navbar-dark navbar-del shadow-sm mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="{{ route('buku.index') }}">SIPUS-Del</a>

        @auth
            <div class="navbar-nav me-auto">
                <a class="nav-link {{ request()->routeIs('buku.*') ? 'active fw-semibold' : '' }}" href="{{ route('buku.index') }}">Buku</a>
                <a class="nav-link {{ request()->routeIs('kategori.*') ? 'active fw-semibold' : '' }}" href="{{ route('kategori.index') }}">Kategori</a>
                <a class="nav-link {{ request()->routeIs('peminjaman.*') ? 'active fw-semibold' : '' }}" href="{{ route('peminjaman.index') }}">Peminjaman</a>
            </div>

            <div class="d-flex align-items-center gap-3 text-white small">
                <span>{{ auth()->user()->name }}</span>
                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-light">Logout</button>
                </form>
            </div>
        @endauth
    </div>
</nav>
@auth
    <div class="container mb-3">
        <div class="small text-muted border rounded bg-white px-3 py-2">
            <strong>Sesi:</strong>
            login sejak {{ session('login_timestamp', '-') }}
            &middot; pencarian terakhir: {{ session('last_search', '-') }}
            &middot; peminjam terakhir: {{ session('last_borrower', '-') }}
        </div>
    </div>
@endauth

    <div class="container pb-5">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
              {{ session('error') }}
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
    @endif

        @if($errors->any())
            <div class="alert alert-danger">Periksa kembali isian formulir, ada data yang belum valid.</div>
        @endif

        {{ $slot }}
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>