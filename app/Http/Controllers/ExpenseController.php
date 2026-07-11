<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Expense;
use App\Services\PushNotificationService;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);
        $search = $request->get('search');
        $userCode = session('access_code');

        $base = Expense::with('category')
            ->whereMonth('date', $month)
            ->whereYear('date', $year);
        if ($userCode) $base->where(fn($q) => $q->where('user_code', $userCode)->orWhereNull('user_code'));

        $expenses = (clone $base)
            ->when($search, fn($q) => $q->where('description', 'like', "%{$search}%"))
            ->latest('date')
            ->latest('created_at')
            ->paginate(20);

        $total = (clone $base)
            ->when($search, fn($q) => $q->where('description', 'like', "%{$search}%"))
            ->sum('amount');

        $categories = Category::all();

        return view('expenses.index', compact('expenses', 'categories', 'total', 'month', 'year', 'search'));
    }

    public function store(Request $request, PushNotificationService $push)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|integer|min:1',
            'description' => 'nullable|string|max:255',
            'date' => 'nullable|date',
        ]);

        $validated['date'] ??= now()->toDateString();
        $validated['source'] = 'web';
        $validated['user_code'] = session('access_code');

        $expense = Expense::create($validated);

        $cat = $expense->category;
        $msg = "Rp " . number_format($expense->amount, 0, ',', '.') . " — {$cat->emoji} {$cat->name}";
        if ($expense->description) $msg .= "\n" . $expense->description;
        $push->sendToAll('💰 Pengeluaran Baru', $msg);

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
