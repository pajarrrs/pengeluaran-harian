@extends('layouts.app')
@section('title', 'Edit Pengeluaran')

@section('content')
    <div class="max-w-lg mx-auto">
        <h1 class="text-2xl font-bold mb-6">Edit Pengeluaran</h1>

        <form method="POST" action="{{ route('expenses.update', $expense) }}" class="bg-white rounded-lg shadow p-6 space-y-4">
            @csrf @method('PUT')

            <div>
                <label class="block text-sm font-medium mb-1">Kategori</label>
                <select name="category_id" required class="w-full border rounded px-3 py-2 text-sm">
                    @foreach ($categories as $c)
                        <option value="{{ $c->id }}" {{ $c->id == $expense->category_id ? 'selected' : '' }}>{{ $c->emoji }} {{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Jumlah (Rp)</label>
                <input type="number" name="amount" value="{{ $expense->amount }}" required class="w-full border rounded px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Deskripsi</label>
                <input type="text" name="description" value="{{ $expense->description }}" class="w-full border rounded px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Tanggal</label>
                <input type="date" name="date" value="{{ $expense->date->format('Y-m-d') }}" required class="w-full border rounded px-3 py-2 text-sm">
            </div>

            <div class="flex gap-3">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">Simpan</button>
                <a href="{{ route('expenses.index') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded text-sm hover:bg-gray-300">Batal</a>
            </div>
        </form>
    </div>
@endsection
