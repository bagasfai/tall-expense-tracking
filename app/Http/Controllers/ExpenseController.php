<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    /**
     * Display a listing of expenses
     */
    public function index(Request $request)
    {
        $query = Expense::with('category')
            ->where('user_id', Auth::id());

        // Search
        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by category
        if ($request->has('category_id') && $request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('date', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('date', '<=', $request->end_date);
        }

        // Filter by type
        if ($request->has('type') && $request->type) {
            if ($request->type === 'recurring') {
                $query->recurring();
            } elseif ($request->type === 'one-time') {
                $query->oneTime();
            }
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'date');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        // Pagination
        $perPage = $request->input('per_page', 15);
        $expenses = $query->paginate($perPage);

        // Calculate total
        $totalQuery = Expense::where('user_id', Auth::id());
        
        if ($request->has('search') && $request->search) {
            $totalQuery->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('category_id') && $request->category_id) {
            $totalQuery->where('category_id', $request->category_id);
        }

        if ($request->has('start_date') && $request->start_date) {
            $totalQuery->whereDate('date', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date) {
            $totalQuery->whereDate('date', '<=', $request->end_date);
        }

        if ($request->has('type') && $request->type) {
            if ($request->type === 'recurring') {
                $totalQuery->recurring();
            } elseif ($request->type === 'one-time') {
                $totalQuery->oneTime();
            }
        }

        $total = $totalQuery->sum('amount');

        return response()->json([
            'data' => $expenses->items(),
            'meta' => [
                'current_page' => $expenses->currentPage(),
                'last_page' => $expenses->lastPage(),
                'per_page' => $expenses->perPage(),
                'total' => $expenses->total(),
                'total_amount' => $total,
            ],
        ]);
    }

    /**
     * Store a newly created expense
     */
    public function store(Request $request)
    {
        $rules = [
            'amount' => 'required|numeric|min:0.01',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'required|date',
            'category_id' => 'nullable|exists:categories,id',
            'type' => 'required|in:one-time,recurring',
        ];

        if ($request->type === 'recurring') {
            $rules['recurring_frequency'] = 'required|in:daily,weekly,monthly,yearly';
            $rules['recurring_start_date'] = 'required|date';
            $rules['recurring_end_date'] = 'nullable|date|after:recurring_start_date';
        }

        $request->validate($rules);

        $data = [
            'user_id' => Auth::id(),
            'amount' => $request->amount,
            'title' => $request->title,
            'description' => $request->description,
            'date' => $request->date,
            'category_id' => $request->category_id,
            'type' => $request->type,
        ];

        if ($request->type === 'recurring') {
            $data['recurring_frequency'] = $request->recurring_frequency;
            $data['recurring_start_date'] = $request->recurring_start_date;
            $data['recurring_end_date'] = $request->recurring_end_date;
        }

        $expense = Expense::create($data);
        $expense->load('category');

        return response()->json([
            'message' => 'Expense created successfully',
            'data' => $expense,
        ], 201);
    }

    /**
     * Display the specified expense
     */
    public function show($id)
    {
        $expense = Expense::with('category')
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        return response()->json([
            'data' => $expense,
        ]);
    }

    /**
     * Update the specified expense
     */
    public function update(Request $request, $id)
    {
        $expense = Expense::where('user_id', Auth::id())->findOrFail($id);

        $rules = [
            'amount' => 'required|numeric|min:0.01',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'required|date',
            'category_id' => 'nullable|exists:categories,id',
            'type' => 'required|in:one-time,recurring',
        ];

        if ($request->type === 'recurring') {
            $rules['recurring_frequency'] = 'required|in:daily,weekly,monthly,yearly';
            $rules['recurring_start_date'] = 'required|date';
            $rules['recurring_end_date'] = 'nullable|date|after:recurring_start_date';
        }

        $request->validate($rules);

        $data = [
            'amount' => $request->amount,
            'title' => $request->title,
            'description' => $request->description,
            'date' => $request->date,
            'category_id' => $request->category_id,
            'type' => $request->type,
        ];

        if ($request->type === 'recurring') {
            $data['recurring_frequency'] = $request->recurring_frequency;
            $data['recurring_start_date'] = $request->recurring_start_date;
            $data['recurring_end_date'] = $request->recurring_end_date;
        } else {
            $data['recurring_frequency'] = null;
            $data['recurring_start_date'] = null;
            $data['recurring_end_date'] = null;
        }

        $expense->update($data);
        $expense->load('category');

        return response()->json([
            'message' => 'Expense updated successfully',
            'data' => $expense,
        ]);
    }

    /**
     * Remove the specified expense
     */
    public function destroy($id)
    {
        $expense = Expense::where('user_id', Auth::id())->findOrFail($id);
        $expense->delete();

        return response()->json([
            'message' => 'Expense deleted successfully',
        ]);
    }

    /**
     * Get recurring expenses
     */
    public function recurring(Request $request)
    {
        $query = Expense::with('category')
            ->where('user_id', Auth::id())
            ->recurring();

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        $expenses = $query->get();

        return response()->json([
            'data' => $expenses,
        ]);
    }
}
