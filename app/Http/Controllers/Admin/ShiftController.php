<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domains\Sales\Models\Order;
use App\Domains\Sales\Models\Register;
use App\Domains\Sales\Models\Shift;
use App\Domains\System\Models\SystemNotification;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShiftController extends Controller
{
    public function getCurrentShift(): JsonResponse
    {
        try {
            $user = Auth::user();

            $shift = Shift::where('user_id', $user->id)
                ->whereNull('closed_at')
                ->latest('opened_at')
                ->first();

            if (!$shift) {
                return response()->json([
                    'success' => true,
                    'has_open_shift' => false,
                ]);
            }

            $cashSales = (float) Order::where('user_id', $user->id)
                ->where('payment_method', 'cash')
                ->where('created_at', '>=', $shift->opened_at)
                ->sum('grand_total');

            $ordersCount = Order::where('user_id', $user->id)
                ->where('created_at', '>=', $shift->opened_at)
                ->count();

            $openingCash = (float) ($shift->opening_cash ?? 0);
            $expectedCash = $openingCash + $cashSales;

            return response()->json([
                'success' => true,
                'has_open_shift' => true,
                'shift' => $shift,
                'opening_cash' => $openingCash,
                'cash_sales' => $cashSales,
                'expected_cash' => $expectedCash,
                'orders_count' => $ordersCount,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function openShift(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'opening_cash' => ['required', 'numeric', 'min:0'],
            ]);

            $user = Auth::user();

            // پشکنین ئەگەر پێشتر شیفتی دانەخراوی هەبێت
            $existing = Shift::where('user_id', $user->id)
                ->whereNull('closed_at')
                ->first();

            if ($existing) {
                return response()->json([
                    'success' => true,
                    'message' => 'شیفت پێشتر کراوەتەوە.',
                    'shift' => $existing,
                ]);
            }

            $register = Register::first();
            $registerId = $register ? $register->id : 1;

            $shift = Shift::create([
                'user_id' => $user->id,
                'register_id' => $registerId,
                'opened_at' => now(),
                'opening_cash' => (float) $validated['opening_cash'],
            ]);

            try {
                SystemNotification::create([
                    'type' => 'shift_opened',
                    'title' => 'دەستپێکردنی شیفت: ' . $user->name,
                    'message' => "کاشێر دەستی بە کارکردن کرد بە کاشی سەرەتایی: " . number_format((float) $validated['opening_cash'], 0) . " د.ع",
                    'severity' => 'info',
                ]);
            } catch (\Throwable $th) {}

            return response()->json([
                'success' => true,
                'shift' => $shift,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function closeShift(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'actual_cash' => ['required', 'numeric', 'min:0'],
                'notes' => ['nullable', 'string', 'max:500'],
            ]);

            $user = Auth::user();
            $shift = Shift::where('user_id', $user->id)
                ->whereNull('closed_at')
                ->latest('opened_at')
                ->first();

            if (!$shift) {
                return response()->json(['success' => false, 'message' => 'هیچ شیفتێکی کراوە نەدۆزرایەوە.'], 404);
            }

            $cashSales = (float) Order::where('user_id', $user->id)
                ->where('payment_method', 'cash')
                ->where('created_at', '>=', $shift->opened_at)
                ->sum('grand_total');

            $openingCash = (float) ($shift->opening_cash ?? 0);
            $expectedCash = $openingCash + $cashSales;
            $actualCash = (float) $validated['actual_cash'];
            $difference = $actualCash - $expectedCash;

            $shift->update([
                'closing_cash' => $actualCash,
                'closed_at' => now(),
                'notes' => $validated['notes'] ?? null,
            ]);

            $diffText = '';
            $severity = 'info';

            if ($difference < 0) {
                $severity = 'danger';
                $diffText = " (کورتهێنان: " . number_format(abs($difference), 0) . " د.ع ⚠️)";
            } elseif ($difference > 0) {
                $severity = 'warning';
                $diffText = " (زیادە: +" . number_format($difference, 0) . " د.ع)";
            } else {
                $diffText = " (حیساب بە تەواوی ڕێکە ✓)";
            }

            try {
                SystemNotification::create([
                    'type' => 'shift_closed',
                    'title' => 'داخستنی شیفتی کاشێر: ' . $user->name,
                    'message' => "کاشێر شیفتەکەی داخست بە کاشی کۆتایی: " . number_format($actualCash, 0) . " د.ع" . $diffText,
                    'severity' => $severity,
                ]);
            } catch (\Throwable $th) {}

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return response()->json([
                'success' => true,
                'z_report_url' => route('admin.reports.z_report', $shift->id),
                'difference' => $difference,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
