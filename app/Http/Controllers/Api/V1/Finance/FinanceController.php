<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Finance;

use App\Domains\Finance\Actions\RecordExpenseAction;
use App\Domains\Finance\Models\Account;
use App\Domains\Finance\Models\JournalEntry;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\RecordExpenseRequest;
use Illuminate\Http\JsonResponse;

class FinanceController extends Controller
{
    public function accounts(): JsonResponse
    {
        $accounts = Account::orderBy('code')->get();

        return response()->json([
            'success' => true,
            'data' => $accounts,
        ]);
    }

    public function journalEntries(): JsonResponse
    {
        $entries = JournalEntry::with('lines.account')
            ->latest('posted_at')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $entries,
        ]);
    }

    public function recordExpense(RecordExpenseRequest $request, RecordExpenseAction $action): JsonResponse
    {
        $entry = $action->execute(
            expenseAccountId: $request->validated('expense_account_id'),
            paymentAccountId: $request->validated('payment_account_id'),
            amount: (float) $request->validated('amount'),
            description: $request->validated('description')
        );

        return response()->json([
            'success' => true,
            'message' => 'خەرجی بە سەرکەوتوویی تۆمارکرا و قەیدی ژمێریاری بڕدرا.',
            'data' => $entry,
        ], 201);
    }

    public function trialBalance(): JsonResponse
    {
        $accounts = Account::with(['journalEntryLines'])->get()->map(function ($account) {
            $totalDebit = (float) $account->journalEntryLines->sum('debit');
            $totalCredit = (float) $account->journalEntryLines->sum('credit');

            return [
                'id' => $account->id,
                'code' => $account->code,
                'name' => $account->name,
                'type' => $account->type,
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'net_balance' => round($totalDebit - $totalCredit, 2),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $accounts,
        ]);
    }
}
