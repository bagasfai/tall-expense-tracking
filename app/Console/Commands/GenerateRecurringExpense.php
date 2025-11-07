<?php

namespace App\Console\Commands;

use App\Models\Expense;
use Illuminate\Console\Command;

class GenerateRecurringExpense extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'expenses:generate-recurring-expense';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate recurring expenses based on their schedule';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Generating recurring expenses...');

        $recurringExpenses = Expense::whereNull('deleted_at')
            ->recurring()
            ->get();

        $generatedCount = 0;

        foreach ($recurringExpenses as $recurringExpense) {
            $generated = $this->generateExpensesForRecurring($recurringExpense);
            $generatedCount += $generated;
        }

        $this->info("Successfully generated {$generatedCount} recurring expenses.");
    }

    public function generateExpensesForRecurring(Expense $recurringExpense)
    {
        if (!$recurringExpense->shouldGenerateNextOccurrence()) {
            return 0;
        }

        $nextDate = $recurringExpense->getNextOccurrenceDate();
        $generatedCount = 0;

        while ($nextDate && $nextDate->lte(now())) {
            $exists = Expense::where('parent_expense_id', $recurringExpense->id)
                ->whereDate('date', $nextDate)
                ->exists();

            if (!$exists) {
                $this->createExpenseOccurrence($recurringExpense, $nextDate);
                $generatedCount++;

                $this->line("Generated {$recurringExpense->title} for date {$nextDate->format('Y-m-d')}");
            }

            $nextDate = match ($recurringExpense->recurring_frequency) {
                'daily' => $nextDate->copy()->addDay(),
                'weekly' => $nextDate->copy()->addWeek(),
                'monthly' => $nextDate->copy()->addMonth(),
                'yearly' => $nextDate->copy()->addYear(),
                default => null,
            };

            if ($recurringExpense->recurring_end_date && $nextDate && $nextDate->gt($recurringExpense->recurring_end_date)) {
                break;
            }

            if ($nextDate && $nextDate->gt(now())) {
                break;
            }

            return $generatedCount;
        }
    }

    public function createExpenseOccurrence(Expense $recurringExpense, $nextDate)
    {
        Expense::create([
            'user_id' => $recurringExpense->user_id,
            'category_id' => $recurringExpense->category_id,
            'amount' => $recurringExpense->amount,
            'title' => $recurringExpense->title,
            'description' => $recurringExpense->description,
            'date' => $nextDate,
            'type' => 'one-time',
            'parent_expense_id' => $recurringExpense->id,
            'is_auto_generated' => true,
        ]);
    }
}
