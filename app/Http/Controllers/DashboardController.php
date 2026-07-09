<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Expense;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);
        $dateFilter = fn($q) => $q->whereMonth('date', $month)->whereYear('date', $year);

        $total = Expense::where($dateFilter)->sum('amount');
        $count = Expense::where($dateFilter)->count();
        $daysInMonth = now()->setYear($year)->setMonth($month)->daysInMonth;
        $avgPerDay = $count > 0 ? round($total / max(now()->day, 1)) : 0;
        $highest = Expense::where($dateFilter)->with('category')->orderByDesc('amount')->first();

        $perCategory = Category::with(['expenses' => fn($q) => $q->where($dateFilter)])
            ->get()
            ->map(fn($c) => [
                'name' => $c->name,
                'emoji' => $c->emoji,
                'color' => $c->color,
                'total' => $c->expenses->sum('amount'),
            ]);

        $chartLabels = $perCategory->pluck('name');
        $chartData = $perCategory->pluck('total');
        $chartColors = $perCategory->pluck('color');

        $recentExpenses = Expense::with('category')
            ->where($dateFilter)
            ->latest('date')
            ->latest('created_at')
            ->take(10)
            ->get();

        return view('dashboard.index', compact(
            'total', 'count', 'perCategory', 'avgPerDay', 'highest',
            'chartLabels', 'chartData', 'chartColors',
            'month', 'year', 'recentExpenses'
        ));
    }
}
