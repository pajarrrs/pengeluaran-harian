<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Expense;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);
        $userCode = session('access_code');
        $dateFilter = function ($q) use ($startDate, $endDate, $month, $year, $userCode) {
            if ($startDate && $endDate) {
                $q->whereBetween('date', [$startDate, $endDate]);
            } else {
                $q->whereMonth('date', $month)->whereYear('date', $year);
            }
            if ($userCode) $q->where(fn($q) => $q->where('user_code', $userCode)->orWhereNull('user_code'));
        };

        $total = Expense::where($dateFilter)->sum('amount');
        $count = Expense::where($dateFilter)->count();
        $daysInMonth = now()->setYear($year)->setMonth($month)->daysInMonth;
        
        $currentMonth = !$startDate && $month === now()->month && $year === now()->year;
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

        $todayExpenses = Expense::with('category')
            ->whereDate('date', now()->toDateString())
            ->when($userCode, fn($q) => $q->where(fn($q) => $q->where('user_code', $userCode)->orWhereNull('user_code')))
            ->latest('created_at')
            ->get();
        $todayTotal = $todayExpenses->sum('amount');
        $todayCount = $todayExpenses->count();

        $weekStart = now()->startOfWeek()->toDateString();
        $weekEnd = now()->toDateString();
        $weekQuery = Expense::whereBetween('date', [$weekStart, $weekEnd])
            ->when($userCode, fn($q) => $q->where(fn($q) => $q->where('user_code', $userCode)->orWhereNull('user_code')));
        $weekTotal = (clone $weekQuery)->sum('amount');
        $weekCount = (clone $weekQuery)->count();

        return view('dashboard.index', compact(
            'total', 'count', 'perCategory', 'avgPerDay', 'highest',
            'chartLabels', 'chartData', 'chartColors',
            'month', 'year', 'recentExpenses', 'budgetAlerts',
            'startDate', 'endDate', 'todayExpenses', 'todayTotal', 'todayCount',
            'weekTotal', 'weekCount'
        ));
    }
}
