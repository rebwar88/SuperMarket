<?php

declare(strict_types=1);

namespace App\Domains\Finance\Actions;

use App\Domains\Finance\Models\JournalEntry;
use App\Domains\Finance\Models\JournalEntryLine;
use Illuminate\Support\Facades\DB;

class RecordExpenseAction
{
    public function execute(string $expenseAccountId, string $paymentAccountId, float $amount, string $description): JournalEntry
    {
        return DB::transaction(function () use ($expenseAccountId, $paymentAccountId, $amount, $description) {
            $entry = JournalEntry::create([
                'source_type' => 'expense',
                'description' => $description,
                'posted_at' => now(),
            ]);

            // Debit: Expense Account (زیادبوونی خەرجی)
            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'account_id' => $expenseAccountId,
                'debit' => $amount,
                'credit' => 0.00,
            ]);

            // Credit: Cash / Bank Account (کەمبوونی قاسی سندوق یان بانک)
            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'account_id' => $paymentAccountId,
                'debit' => 0.00,
                'credit' => $amount,
            ]);

            return $entry->load('lines.account');
        });
    }
}
