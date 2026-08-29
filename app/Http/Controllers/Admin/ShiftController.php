<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ShiftController extends Controller
{
    /**
     * هێنانی شیفتی کراوەی ئێستای کاشێر
     */
    public function getCurrentShift(Request $request)
    {
        $shift = DB::table('register_shifts')
            ->join('registers', 'registers.id', '=', 'register_shifts.register_id')
            ->where('register_shifts.user_id', Auth::id())
            ->whereNull('register_shifts.closed_at')
            ->select('register_shifts.*', 'registers.name as register_name')
            ->first();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'shift' => $shift]);
        }

        return view('admin.shifts.current', compact('shift'));
    }

    /**
     * کردنەوەی شیفتێکی نوێ
     */
    public function openShift(Request $request)
    {
        $request->validate([
            'register_id' => 'required|uuid|exists:registers,id',
            'opening_cash' => 'nullable|numeric|min:0'
        ]);

        $userId = Auth::id();

        // پشکنین: ئایا ئەم کاشێرە شیفتی کراوەی هەیە؟
        $activeShift = DB::table('register_shifts')
            ->where('user_id', $userId)
            ->whereNull('closed_at')
            ->exists();

        if ($activeShift) {
            $msg = 'پێشتر شیفتێکی کراوەت هەیە، تکایە سەرەتا ئەوە دابخە.';
            return $request->wantsJson() 
                ? response()->json(['success' => false, 'message' => $msg], 400)
                : back()->with('error', $msg);
        }

        $shiftId = Str::uuid()->toString();

        DB::table('register_shifts')->insert([
            'id' => $shiftId,
            'register_id' => $request->register_id,
            'user_id' => $userId,
            'opened_at' => now(),
            'opening_cash' => (float) $request->input('opening_cash', 0),
            'closing_cash' => null,
            'notes' => $request->input('notes'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $msg = 'شیفت بە سەرکەوتوویی کرایەوە.';
        return $request->wantsJson()
            ? response()->json(['success' => true, 'message' => $msg, 'shift_id' => $shiftId])
            : redirect()->route('pos.index')->with('success', $msg);
    }

    /**
     * داخستنی شیفتی ئێستا
     */
    public function closeShift(Request $request)
    {
        $request->validate([
            'closing_cash' => 'required|numeric|min:0',
            'notes' => 'nullable|string'
        ]);

        $shift = DB::table('register_shifts')
            ->where('user_id', Auth::id())
            ->whereNull('closed_at')
            ->first();

        if (!$shift) {
            $msg = 'هیچ شیفتێکی کراوە نەدۆزرایەوە بۆ داخستن.';
            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => $msg], 404)
                : back()->with('error', $msg);
        }

        DB::table('register_shifts')
            ->where('id', $shift->id)
            ->update([
                'closed_at' => now(),
                'closing_cash' => (float) $request->input('closing_cash'),
                'notes' => clone_notes($shift->notes, $request->input('notes')),
                'updated_at' => now(),
            ]);

        $msg = 'شیفت بە سەرکەوتوویی داخرا.';
        return $request->wantsJson()
            ? response()->json(['success' => true, 'message' => $msg])
            : redirect()->route('admin.reports.z_report', $shift->id)->with('success', $msg);
    }
}

function clone_notes($old, $new) {
    if (!$new) return $old;
    if (!$old) return $new;
    return $old . " | " . $new;
}
