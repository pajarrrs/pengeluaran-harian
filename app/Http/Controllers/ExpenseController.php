<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Expense;
use App\Services\PushNotificationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);
        $search = $request->get('search');
        $userCode = session('access_code');

        $base = Expense::with('category');
        if ($startDate && $endDate) {
            $base->whereBetween('date', [$startDate, $endDate]);
        } else {
            $base->whereMonth('date', $month)->whereYear('date', $year);
        }
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

        return view('expenses.index', compact('expenses', 'categories', 'total', 'month', 'year', 'search', 'startDate', 'endDate'));
    }

    public function store(Request $request, PushNotificationService $push)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|integer|min:1',
            'description' => 'nullable|string|max:255',
            'date' => 'nullable|date',
            'is_recurring' => 'nullable|boolean',
            'recurring_interval' => 'nullable|required_if:is_recurring,1|integer|min:1|max:365',
        ]);

        $validated['date'] ??= now()->toDateString();
        $validated['source'] = 'web';
        $validated['user_code'] = session('access_code');

        $isRecurring = $validated['is_recurring'] ?? false;
        unset($validated['is_recurring']);
        if ($isRecurring) {
            $validated['is_recurring'] = true;
            $interval = $validated['recurring_interval'];
            $validated['next_date'] = now()->parse($validated['date'])->addDays($interval)->toDateString();
        }

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

    public function inlineUpdate(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            'amount' => 'required|integer|min:1',
        ]);

        $expense->update(['amount' => $validated['amount']]);

        return response()->json(['success' => true, 'amount' => number_format($expense->amount, 0, ',', '.')]);
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();

        return redirect()->back()->with('success', 'Pengeluaran berhasil dihapus.');
    }

    public function exportPdf(Request $request)
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);
        $userCode = session('access_code');

        $q = Expense::with('category');
        if ($startDate && $endDate) {
            $q->whereBetween('date', [$startDate, $endDate]);
        } else {
            $q->whereMonth('date', $month)->whereYear('date', $year);
        }
        if ($userCode) $q->where(fn($q) => $q->where('user_code', $userCode)->orWhereNull('user_code'));
        $expenses = $q->latest('date')->latest('created_at')->get();
        $total = $expenses->sum('amount');

        $pdf = Pdf::loadView('expenses.pdf', compact('expenses', 'total', 'month', 'year', 'startDate', 'endDate'));

        $filename = 'pengeluaran' . ($startDate && $endDate ? "-{$startDate}_{$endDate}" : "-{$month}-{$year}") . '.pdf';

        return $pdf->download($filename);
    }

    public function importCsv(Request $request)
    {
        $request->validate([
            'csv' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('csv');
        $handle = fopen($file->getPathname(), 'r');
        $header = fgetcsv($handle, 0, ';');
        $categories = Category::all()->keyBy(fn($c) => strtolower($c->name));
        $userCode = session('access_code');
        $imported = 0;

        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            $data = array_combine($header, $row);
            if (!$data || empty($data['Jumlah'])) continue;

            $amount = (int) str_replace(['.', ' ', 'Rp'], '', $data['Jumlah'] ?? '0');
            if ($amount <= 0) continue;

            $categoryName = trim($data['Kategori'] ?? '');
            $category = $categoryName
                ? ($categories[strtolower($categoryName)] ?? Category::first())
                : Category::first();
            if (!$category) continue;

            Expense::create([
                'category_id' => $category->id,
                'amount' => $amount,
                'description' => trim($data['Deskripsi'] ?? '') ?: null,
                'date' => $data['Tanggal'] ?? now()->toDateString(),
                'source' => trim($data['Sumber'] ?? 'web') ?: 'web',
                'user_code' => $userCode,
            ]);

            $imported++;
        }

        fclose($handle);

        $msg = "Berhasil import {$imported} pengeluaran.";
        return redirect()->back()->with('success', $msg);
    }
}
