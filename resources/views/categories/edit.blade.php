@extends('layouts.app')
@section('title', 'Edit Kategori')

@section('content')
    <div class="max-w-lg mx-auto">
        <h1 class="text-2xl font-bold mb-6">Edit Kategori</h1>

        <form method="POST" action="{{ route('categories.update', $category) }}" class="bg-white rounded-lg shadow p-6 space-y-4">
            @csrf @method('PUT')

            <div>
                <label class="block text-sm font-medium mb-1">Nama Kategori</label>
                <input type="text" name="name" value="{{ $category->name }}" required class="w-full border rounded px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Emoji</label>
                <input type="text" name="emoji" value="{{ $category->emoji }}" class="w-full border rounded px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Warna (hex)</label>
                <input type="text" name="color" value="{{ $category->color }}" class="w-full border rounded px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Budget (Rp)</label>
                <input type="number" name="budget" value="{{ $category->budget }}" class="w-full border rounded px-3 py-2 text-sm">
            </div>

            <div class="flex gap-3">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">Simpan</button>
                <a href="{{ route('categories.index') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded text-sm hover:bg-gray-300">Batal</a>
            </div>
        </form>
    </div>
@endsection
