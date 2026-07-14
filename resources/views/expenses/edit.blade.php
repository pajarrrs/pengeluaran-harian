@extends('layouts.app')
@section('title', 'Edit Pengeluaran')

@section('content')
    <div class="max-w-lg mx-auto">
        <h1 class="text-xl font-semibold mb-5">Edit Pengeluaran</h1>

        <form method="POST" action="{{ route('expenses.update', $expense) }}" class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-5 space-y-3.5">
            @csrf @method('PUT')

            <div>
                <label class="block text-sm font-medium mb-1">Kategori</label>
                <select name="category_id" required class="w-full border border-gray-300 dark:border-gray-700 rounded-lg px-2.5 py-2 text-sm bg-white dark:bg-gray-900 dark:text-gray-300 outline-none focus:border-blue-500">
                    @foreach ($categories as $c)
                        <option value="{{ $c->id }}" {{ $c->id == $expense->category_id ? 'selected' : '' }}>{{ $c->emoji }} {{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Jumlah (Rp)</label>
                <input type="number" name="amount" value="{{ $expense->amount }}" required class="w-full border border-gray-300 dark:border-gray-700 rounded-lg px-2.5 py-2 text-sm bg-white dark:bg-gray-900 dark:text-gray-300 outline-none focus:border-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Deskripsi</label>
                <input type="text" name="description" value="{{ $expense->description }}" class="w-full border border-gray-300 dark:border-gray-700 rounded-lg px-2.5 py-2 text-sm bg-white dark:bg-gray-900 dark:text-gray-300 outline-none focus:border-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Tanggal</label>
                <input type="date" name="date" value="{{ $expense->date->format('Y-m-d') }}" required class="w-full border border-gray-300 dark:border-gray-700 rounded-lg px-2.5 py-2 text-sm bg-white dark:bg-gray-900 dark:text-gray-300 outline-none focus:border-blue-500">
            </div>

            <div class="flex flex-wrap items-center gap-3 mt-2">
                <label class="flex items-center gap-1.5 text-sm text-gray-600 dark:text-gray-400">
                    <input type="checkbox" onchange="var f=document.getElementById('editIntervalField');var s=f.querySelector('select');f.classList.toggle('hidden',!this.checked);s.disabled=!this.checked" {{ $expense->is_recurring ? 'checked' : '' }}>
                    <span>Berulang</span>
                </label>
                <div id="editIntervalField" class="{{ $expense->is_recurring ? '' : 'hidden' }} flex items-center gap-1.5">
                    <span class="text-sm text-gray-500">setiap</span>
                    <select name="recurring_interval" class="border border-gray-300 dark:border-gray-700 rounded-lg px-2 py-1.5 text-sm bg-white dark:bg-gray-900 dark:text-gray-300 outline-none" {{ $expense->is_recurring ? '' : 'disabled' }}>
                        <option value="7" {{ $expense->recurring_interval == 7 ? 'selected' : '' }}>7 hari</option>
                        <option value="14" {{ $expense->recurring_interval == 14 ? 'selected' : '' }}>14 hari</option>
                        <option value="30" {{ $expense->recurring_interval == 30 ? 'selected' : '' }}>30 hari</option>
                        <option value="60" {{ $expense->recurring_interval == 60 ? 'selected' : '' }}>60 hari</option>
                        <option value="365" {{ $expense->recurring_interval == 365 ? 'selected' : '' }}>365 hari</option>
                    </select>
                </div>
            </div>

            <div class="flex gap-2 pt-1">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700">Simpan</button>
                <a href="{{ route('expenses.index') }}" class="bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 px-4 py-2 rounded-lg text-sm hover:bg-gray-200 dark:hover:bg-gray-700">Batal</a>
            </div>
        </form>
    </div>
@endsection