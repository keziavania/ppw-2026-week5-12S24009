<x-layout title="Tambah Kategori">
    <h1 class="h4 mb-3 text-del">Tambah Kategori</h1>
    <form action="{{ route('kategori.store') }}" method="POST" class="card card-body shadow-sm">
        @csrf
        @include('kategori._form')
    </form>
</x-layout>