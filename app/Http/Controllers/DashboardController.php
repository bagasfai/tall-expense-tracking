<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Budget;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics
     */
    public function index(Request $request)
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);
        $userId = Auth::id();

        // Get total expenses for the month
        $totalExpenses = Expense::where('user_id', $userId)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->sum('amount');

        // Get total budget for the month
        $totalBudget = Budget::where('user_id', $userId)
            ->where('month', $month)
            ->where('year', $year)
            ->sum('amount');

        // Get category-wise expenses
        $categoryExpenses = Category::where('user_id', $userId)
            ->withSum(['expenses' => function ($query) use ($month, $year) {
                $query->whereYear('date', $year)
                    ->whereMonth('date', $month);
            }], 'amount')
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'color' => $category->color,
                    'icon' => $category->icon,
                    'total' => $category->expenses_sum_amount ?? 0,
                ];
            })
            ->filter(function ($category) {
                return $category['total'] > 0;
            })
            ->values();

        // Get recent expenses
        $recentExpenses = Expense::with('category')
            ->where('user_id', $userId)
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Get budget status
        $budgets = Budget::with('category')
            ->where('user_id', $userId)
            ->where('month', $month)
            ->where('year', $year)
            ->get()
            ->map(function ($budget) {
                return [
                    'id' => $budget->id,
                    'category' => $budget->category,
                    'amount' => $budget->amount,
                    'spent' => $budget->getSpentAmount(),
                    'remaining' => $budget->getRemainingAmount(),
                    'percentage' => $budget->getPercentageUsed(),
                    'is_over' => $budget->isOverBudget(),
                ];
            });

        // Get daily expenses for the month (for charts)
        $dailyExpenses = Expense::where('user_id', $userId)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->selectRaw('DATE(date) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('total', 'date');

        // Get expense trends (last 6 months)
        $trends = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::create($year, $month, 1)->subMonths($i);
            $monthTotal = Expense::where('user_id', $userId)
                ->whereYear('date', $date->year)
                ->whereMonth('date', $date->month)
                ->sum('amount');
            
            $trends[] = [
                'month' => $date->format('M Y'),
                'total' => $monthTotal,
            ];
        }

        return response()->json([
            'data' => [
                'total_expenses' => $totalExpenses,
                'total_budget' => $totalBudget,
                'remaining_budget' => $totalBudget - $totalExpenses,
                'budget_percentage' => $totalBudget > 0 ? round(($totalExpenses / $totalBudget) * 100, 1) : 0,
                'category_expenses' => $categoryExpenses,
                'recent_expenses' => $recentExpenses,
                'budgets' => $budgets,
                'daily_expenses' => $dailyExpenses,
                'trends' => $trends,
                'month' => $month,
                'year' => $year,
            ],
        ]);
    }
}
