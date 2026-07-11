<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Expense;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);
        $dateFilter = function ($q) use ($month, $year) {
            $q->whereMonth('date', $month)->whereYear('date', $year);
        };

        $total = Expense::where($dateFilter)->sum('amount');
        $count = Expense::where($dateFilter)->count();
        $daysInMonth = now()->setYear($year)->setMonth($month)->daysInMonth;
        
        $currentMonth = $month === now()->month && $year === now()->year;
        $divider = $currentMonth ? max(now()->day, 1) : $daysInMonth;
        $avgPerDay = $count > 0 ? round($total / $divider) : 0;
        
        $highest = Expense::where($dateFilter)->with('category')->orderByDesc('amount')->first();

        $perCategory = Category::with(['expenses' => fn($q) => $q->where($dateFilter)])
            ->get()
            ->map(fn($c) => [
                'name' => $c->name,
                'emoji' => $c->emoji,
                'color' => $c->color,
                'budget' => $c->budget,
                'total' => $c->expenses->sum('amount'),
            ]);

        $chartLabels = $perCategory->pluck('name');
        $chartData = $perCategory->pluck('total');
        $chartColors = $perCategory->pluck('color');
        $budgetAlerts = $perCategory->filter(fn($c) => $c['budget'] && $c['total'] >= $c['budget'] * 0.8)
            ->values();

        $recentExpenses = Expense::with('category')
            ->where($dateFilter)
            ->latest('date')
            ->latest('created_at')
            ->take(10)
            ->get();

        return view('dashboard.index', compact(
            'total', 'count', 'perCategory', 'avgPerDay', 'highest',
            'chartLabels', 'chartData', 'chartColors',
            'month', 'year', 'recentExpenses', 'budgetAlerts'
        ));
    }
}
