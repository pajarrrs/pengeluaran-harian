<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);
        $search = $request->get('search');

        $expenses = Expense::with('category')
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->when($search, fn($q) => $q->where('description', 'like', "%{$search}%"))
            ->latest('date')
            ->latest('created_at')
            ->paginate(20);

        $total = Expense::whereMonth('date', $month)
            ->whereYear('date', $year)
            ->when($search, fn($q) => $q->where('description', 'like', "%{$search}%"))
            ->sum('amount');

        $categories = Category::all();

        return view('expenses.index', compact('expenses', 'categories', 'total', 'month', 'year', 'search'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|integer|min:1',
            'description' => 'nullable|string|max:255',
            'date' => 'nullable|date',
        ]);

        $validated['date'] ??= now()->toDateString();
        $validated['source'] = 'web';

        Expense::create($validated);

        return redirect()->back()->with('success', 'Pengeluaran berhasil dicatat.');
    }

    public function edit(Expense $expense)
    {
        $categories = Category::all();
        return view('expenses.edit', compact('expense', 'categories'));
    }

    public function update(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|integer|min:1',
            'description' => 'nullable|string|max:255',
            'date' => 'required|date',
        ]);

        $expense->update($validated);

        return redirect()->route('expenses.index')->with('success', 'Pengeluaran berhasil diubah.');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();

        return redirect()->back()->with('success', 'Pengeluaran berhasil dihapus.');
    }
}
