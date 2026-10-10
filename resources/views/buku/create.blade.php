<x-layout title="Tambah Buku">
    <h1 class="h4 mb-3 text-del">Tambah Buku</h1>
    <form action="{{ route('buku.store') }}" method="POST" class="card card-body shadow-sm">
        @csrf
        @include('buku._form')
    </form>
</x-layout>