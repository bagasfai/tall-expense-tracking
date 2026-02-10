<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BudgetController extends Controller
{
    /**
     * Display a listing of budgets
     */
    public function index(Request $request)
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $budgets = Budget::with('category')
            ->where('user_id', Auth::id())
            ->where('month', $month)
            ->where('year', $year)
            ->get()
            ->map(function ($budget) {
                $budget->spent = $budget->getSpentAmount();
                $budget->remaining = $budget->getRemainingAmount();
                $budget->percentage = $budget->getPercentageUsed();
                $budget->is_over = $budget->isOverBudget();
                return $budget;
            });

        $totalBudget = $budgets->sum('amount');
        $totalSpent = $budgets->sum('spent');
        $totalRemaining = $budgets->sum('remaining');
        $overallPercentage = $totalBudget > 0 ? round(($totalSpent / $totalBudget) * 100, 1) : 0;

        return response()->json([
            'data' => $budgets,
            'summary' => [
                'total_budget' => $totalBudget,
                'total_spent' => $totalSpent,
                'total_remaining' => $totalRemaining,
                'overall_percentage' => $overallPercentage,
                'month' => $month,
                'year' => $year,
            ],
        ]);
    }

    /**
     * Store a newly created budget
     */
    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2100',
            'category_id' => 'nullable|exists:categories,id|unique:budgets,category_id,NULL,id,user_id,' . Auth::id() . ',month,' . $request->month . ',year,' . $request->year,
        ], [
            'category_id.unique' => 'You already have a budget for this category in this month.',
        ]);

        $budget = Budget::create([
            'user_id' => Auth::id(),
            'amount' => $request->amount,
            'month' => $request->month,
            'year' => $request->year,
            'category_id' => $request->category_id,
        ]);

        $budget->load('category');
        $budget->spent = $budget->getSpentAmount();
        $budget->remaining = $budget->getRemainingAmount();
        $budget->percentage = $budget->getPercentageUsed();
        $budget->is_over = $budget->isOverBudget();

        return response()->json([
            'message' => 'Budget created successfully',
            'data' => $budget,
        ], 201);
    }

    /**
     * Display the specified budget
     */
    public function show($id)
    {
        $budget = Budget::with('category')
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        $budget->spent = $budget->getSpentAmount();
        $budget->remaining = $budget->getRemainingAmount();
        $budget->percentage = $budget->getPercentageUsed();
        $budget->is_over = $budget->isOverBudget();

        return response()->json([
            'data' => $budget,
        ]);
    }

    /**
     * Update the specified budget
     */
    public function update(Request $request, $id)
    {
        $budget = Budget::where('user_id', Auth::id())->findOrFail($id);

        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2100',
            'category_id' => 'nullable|exists:categories,id|unique:budgets,category_id,' . $id . ',id,user_id,' . Auth::id() . ',month,' . $request->month . ',year,' . $request->year,
        ], [
            'category_id.unique' => 'You already have a budget for this category in this month.',
        ]);

        $budget->update([
            'amount' => $request->amount,
            'month' => $request->month,
            'year' => $request->year,
            'category_id' => $request->category_id,
        ]);

        $budget->load('category');
        $budget->spent = $budget->getSpentAmount();
        $budget->remaining = $budget->getRemainingAmount();
        $budget->percentage = $budget->getPercentageUsed();
        $budget->is_over = $budget->isOverBudget();

        return response()->json([
            'message' => 'Budget updated successfully',
            'data' => $budget,
        ]);
    }

    /**
     * Remove the specified budget
     */
    public function destroy($id)
    {
        $budget = Budget::where('user_id', Auth::id())->findOrFail($id);
        $budget->delete();

        return response()->json([
            'message' => 'Budget deleted successfully',
        ]);
    }
}
