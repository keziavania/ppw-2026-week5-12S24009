<x-layout title="Edit Kategori">
    <h1 class="h4 mb-3 text-del">Edit Kategori</h1>
    <form action="{{ route('kategori.update', $kategori) }}" method="POST" class="card card-body shadow-sm">
        @csrf
        @method('PUT')
        @include('kategori._form')
    </form>
</x-layout>