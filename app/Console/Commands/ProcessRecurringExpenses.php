<?php

namespace App\Console\Commands;

use App\Models\Expense;
use Illuminate\Console\Command;

class ProcessRecurringExpenses extends Command
{
    protected $signature = 'app:process-recurring';
    protected $description = 'Create new expenses from recurring ones';

    public function handle(): int
    {
        $recurring = Expense::where('is_recurring', true)
            ->whereNotNull('next_date')
            ->where('next_date', '<=', now()->toDateString())
            ->get();

        if ($recurring->isEmpty()) {
            $this->info('No recurring expenses due.');
            return 0;
        }

        foreach ($recurring as $parent) {
            $child = $parent->replicate(['created_at', 'updated_at', 'next_date']);
            $child->date = $parent->next_date;
            $child->parent_id = $parent->id;
            $child->is_recurring = false;
            $child->saveQuietly();

            $parent->update(['next_date' => now()->parse($parent->next_date)->addDays($parent->recurring_interval)]);

            $this->line("Created: {$parent->category->name} Rp {$parent->amount}");
        }

        $this->info("Created {$recurring->count()} recurring expense(s).");
        return 0;
    }
}
