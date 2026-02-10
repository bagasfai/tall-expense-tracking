<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories
     */
    public function index()
    {
        $categories = Category::withCount('expenses')
            ->where('user_id', Auth::id())
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $categories,
        ]);
    }

    /**
     * Store a newly created category
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,NULL,id,user_id,' . Auth::id(),
            'color' => 'required|string',
            'icon' => 'nullable|string|max:255',
        ]);

        $category = Category::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'color' => $request->color,
            'icon' => $request->icon,
        ]);

        return response()->json([
            'message' => 'Category created successfully',
            'data' => $category->load('expenses'),
        ], 201);
    }

    /**
     * Display the specified category
     */
    public function show($id)
    {
        $category = Category::where('user_id', Auth::id())
            ->withCount('expenses')
            ->findOrFail($id);

        return response()->json([
            'data' => $category,
        ]);
    }

    /**
     * Update the specified category
     */
    public function update(Request $request, $id)
    {
        $category = Category::where('user_id', Auth::id())->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $id . ',id,user_id,' . Auth::id(),
            'color' => 'required|string',
            'icon' => 'nullable|string|max:255',
        ]);

        $category->update([
            'name' => $request->name,
            'color' => $request->color,
            'icon' => $request->icon,
        ]);

        return response()->json([
            'message' => 'Category updated successfully',
            'data' => $category,
        ]);
    }

    /**
     * Remove the specified category
     */
    public function destroy($id)
    {
        $category = Category::where('user_id', Auth::id())->findOrFail($id);

        if ($category->expenses()->count() > 0) {
            throw ValidationException::withMessages([
                'category' => ['Cannot delete category with associated expenses.'],
            ]);
        }

        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully',
        ]);
    }
}
