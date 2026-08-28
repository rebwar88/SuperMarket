<?php

declare(strict_types=1);

namespace App\Domains\Finance\Actions;

use App\Domains\Finance\DTOs\JournalEntryData;
use App\Domains\Finance\Models\JournalEntry;
use App\Domains\Finance\Models\JournalEntryLine;
use Illuminate\Support\Facades\DB;

class RecordJournalEntryAction
{
    public function execute(JournalEntryData $data): JournalEntry
    {
        return DB::transaction(function () use ($data) {
            $entry = JournalEntry::create([
                'order_id' => $data->order_id,
                'purchase_order_id' => $data->purchase_order_id,
                'source_type' => $data->source_type,
                'posted_at' => now(),
            ]);

            foreach ($data->lines as $line) {
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $line['account_id'],
                    'debit' => $line['debit'] ?? 0.0,
                    'credit' => $line['credit'] ?? 0.0,
                ]);
            }

            return $entry;
        });
    }
}
