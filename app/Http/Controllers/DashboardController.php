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
        $categoryId = $request->get('category_id');
        $userCode = session('access_code');

        $dateFilter = function ($q) use ($startDate, $endDate, $month, $year, $userCode, $categoryId) {
            if ($startDate && $endDate) {
                $q->whereBetween('date', [$startDate, $endDate]);
            } else {
                $q->whereMonth('date', $month)->whereYear('date', $year);
            }
            if ($userCode) $q->where(fn($q) => $q->where('user_code', $userCode)->orWhereNull('user_code'));
            if ($categoryId) $q->where('category_id', $categoryId);
        };

        $expenseTotal = Expense::whereHas('category', fn($q) => $q->where('type', 'expense'))->where($dateFilter)->sum('amount');
        $incomeTotal = Expense::whereHas('category', fn($q) => $q->where('type', 'income'))->where($dateFilter)->sum('amount');
        $saldo = $incomeTotal - $expenseTotal;
        $count = Expense::where($dateFilter)->count();
        $daysInMonth = now()->setYear($year)->setMonth($month)->daysInMonth;

        $currentMonth = !$startDate && $month === now()->month && $year === now()->year;
        $divider = $currentMonth ? max(now()->day, 1) : $daysInMonth;
        $avgPerDay = $count > 0 ? round($expenseTotal / $divider) : 0;

        $perCategory = Category::with(['expenses' => fn($q) => $q->where($dateFilter)])
            ->get()
            ->map(fn($c) => [
                'name' => $c->name,
                'type' => $c->type,
                'emoji' => $c->emoji,
                'color' => $c->color,
                'budget' => $c->budget,
                'total' => $c->expenses->sum('amount'),
            ]);

        $expenseCategories = $perCategory->filter(fn($c) => $c['type'] === 'expense');
        
        $chartLabels = $expenseCategories->pluck('name');
        $chartData = $expenseCategories->pluck('total');
        $chartColors = $expenseCategories->pluck('color');
        
        $budgetAlerts = $expenseCategories->filter(fn($c) => $c['budget'] && $c['total'] >= $c['budget'] * 0.8)->values();

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
        $todayExpenseTotal = $todayExpenses->filter(fn($e) => $e->category->type === 'expense')->sum('amount');
        $todayIncomeTotal = $todayExpenses->filter(fn($e) => $e->category->type === 'income')->sum('amount');
        $todayCount = $todayExpenses->count();

        $weekStart = now()->startOfWeek()->toDateString();
        $weekEnd = now()->toDateString();
        $weekQuery = Expense::whereBetween('date', [$weekStart, $weekEnd])
            ->when($userCode, fn($q) => $q->where(fn($q) => $q->where('user_code', $userCode)->orWhereNull('user_code')));
        $weekExpenseTotal = (clone $weekQuery)->whereHas('category', fn($q) => $q->where('type', 'expense'))->sum('amount');
        $weekIncomeTotal = (clone $weekQuery)->whereHas('category', fn($q) => $q->where('type', 'income'))->sum('amount');
        $weekCount = (clone $weekQuery)->count();

        $weeklyLabels = [];
        $weeklyData = [];
        for ($i = 3; $i >= 0; $i--) {
            $start = now()->subWeeks($i)->startOfWeek()->toDateString();
            $end = now()->subWeeks($i)->endOfWeek()->toDateString();
            $weeklyLabels[] = 'Minggu ' . now()->subWeeks($i)->weekOfYear;
            $q = Expense::whereBetween('date', [$start, $end])
                ->whereHas('category', fn($q) => $q->where('type', 'expense'))
                ->when($userCode, fn($q) => $q->where(fn($q) => $q->where('user_code', $userCode)->orWhereNull('user_code')));
            if ($categoryId) $q->where('category_id', $categoryId);
            $weeklyData[] = (int) (clone $q)->sum('amount');
        }

        $categories = Category::orderBy('name')->get();

        return view('dashboard.index', compact(
            'expenseTotal', 'incomeTotal', 'saldo', 'count', 'perCategory', 'avgPerDay',
            'chartLabels', 'chartData', 'chartColors',
            'month', 'year',
            'recentExpenses', 'budgetAlerts',
            'startDate', 'endDate',
            'todayExpenses', 'todayExpenseTotal', 'todayIncomeTotal', 'todayCount',
            'weekExpenseTotal', 'weekIncomeTotal', 'weekCount',
            'weeklyLabels', 'weeklyData',
            'categories', 'categoryId'
        ));
    }
}
