<x-layout title="Edit Buku">
    <h1 class="h4 mb-3 text-del">Edit Buku</h1>
    <form action="{{ route('buku.update', $buku) }}" method="POST" class="card card-body shadow-sm">
        @csrf
        @method('PUT')
        @include('buku._form')
    </form>
</x-layout>