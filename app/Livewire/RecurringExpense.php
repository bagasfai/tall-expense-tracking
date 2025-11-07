<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Expense;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class RecurringExpense extends Component
{
    public $showDeleteModal = false;
    public $expenseToDelete = null;

    public function confirmDelete($expenseId)
    {
        $this->expenseToDelete = $expenseId;
        $this->showDeleteModal = true;
    }

    public function deleteExpense()
    {
        if ($this->expenseToDelete) {
            $expense = Expense::findOrFail($this->expenseToDelete);
            if ($expense->user_id === Auth::user()->id) {
                abort(403);
            }

            $expense->childExpenses()->delete();
            $expense->delete();

            session()->flash('message', 'Recurring expense deleted successfully.');
            $this->showDeleteModal = false;
        }
    }

    #[Computed]
    public function recurringExpenses()
    {
        return Expense::with(['category', 'childExpenses'])
            ->forUser(Auth::user()->id)
            ->recurring()
            ->get();
    }

    #[Computed]
    public function categories()
    {
        return Category::where('user_id', Auth::user()->id)
            ->orderBy('name')
            ->get();
    }

    public function render()
    {
        return view('livewire.recurring-expense', [
            'recurringExpenses' => $this->recurringExpenses,
            'categories' => $this->categories,
        ]);
    }
}
