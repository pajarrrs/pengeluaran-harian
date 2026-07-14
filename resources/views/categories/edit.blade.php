@extends('layouts.app')
@section('title', 'Edit Kategori')

@section('content')
    <div class="max-w-lg mx-auto">
        <h1 class="text-xl font-semibold mb-5">Edit Kategori</h1>

        <form method="POST" action="{{ route('categories.update', $category) }}" class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-5 space-y-3.5">
            @csrf @method('PUT')

            <div>
                <label class="block text-sm font-medium mb-1">Nama Kategori</label>
                <input type="text" name="name" value="{{ $category->name }}" required class="w-full border border-gray-300 dark:border-gray-700 rounded-lg px-2.5 py-2 text-sm bg-white dark:bg-gray-900 dark:text-gray-300 outline-none focus:border-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Tipe</label>
                <select name="type" class="w-full border border-gray-300 dark:border-gray-700 rounded-lg px-2.5 py-2 text-sm bg-white dark:bg-gray-900 dark:text-gray-300 outline-none focus:border-blue-500">
                    <option value="expense" {{ $category->type === 'expense' ? 'selected' : '' }}>Pengeluaran</option>
                    <option value="income" {{ $category->type === 'income' ? 'selected' : '' }}>Pemasukan</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Emoji</label>
                <input type="text" name="emoji" value="{{ $category->emoji }}" class="w-full border border-gray-300 dark:border-gray-700 rounded-lg px-2.5 py-2 text-sm bg-white dark:bg-gray-900 dark:text-gray-300 outline-none focus:border-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Warna (hex)</label>
                <input type="text" name="color" value="{{ $category->color }}" class="w-full border border-gray-300 dark:border-gray-700 rounded-lg px-2.5 py-2 text-sm bg-white dark:bg-gray-900 dark:text-gray-300 outline-none focus:border-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Budget (Rp)</label>
                <input type="number" name="budget" value="{{ $category->budget }}" class="w-full border border-gray-300 dark:border-gray-700 rounded-lg px-2.5 py-2 text-sm bg-white dark:bg-gray-900 dark:text-gray-300 outline-none focus:border-blue-500">
            </div>

            <div class="flex gap-2 pt-1">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700">Simpan</button>
                <a href="{{ route('categories.index') }}" class="bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 px-4 py-2 rounded-lg text-sm hover:bg-gray-200 dark:hover:bg-gray-700">Batal</a>
            </div>
        </form>
    </div>
@endsection