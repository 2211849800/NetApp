<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\CashRegisterClosure;
use App\Models\CashRegisterEntry;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MyBalanceController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $month = $request->input('month', now()->format('Y-m'));

        [$year, $m] = explode('-', $month);

        $payments = Payment::query()
            ->where('processed_by', $user->id)
            ->whereYear('paid_at', $year)
            ->whereMonth('paid_at', $m)
            ->orderByDesc('paid_at')
            ->get();

        $stats = [
            'total_balance' => $payments->sum('amount'),
            'total_cash' => $payments->where('gateway', 'cash')->sum('amount'),
            'total_bank' => $payments->whereIn('gateway', ['bank_transfer', 'lypay', 'onepay'])->sum('amount'),
            'total_count' => $payments->count(),
        ];

        $openCashEntries = CashRegisterEntry::query()
            ->forUser($user->id)
            ->open()
            ->where('type', 'cash')
            ->orderByDesc('created_at')
            ->get();

        $openCardEntries = CashRegisterEntry::query()
            ->forUser($user->id)
            ->open()
            ->where('type', 'card')
            ->orderByDesc('created_at')
            ->get();

        $openCashTotal = (float) $openCashEntries->sum('amount');
        $openCardTotal = (float) $openCardEntries->sum('amount');

        $lastClosure = CashRegisterClosure::query()
            ->where('user_id', $user->id)
            ->orderByDesc('closed_at')
            ->first();

        $openTransfersTotal = $this->transfersSinceLastClosure(
            $user->id,
            $lastClosure?->closed_at
        );

        return view('staff.my_balance', compact(
            'user',
            'month',
            'payments',
            'stats',
            'openCashEntries',
            'openCardEntries',
            'openCashTotal',
            'openCardTotal',
            'openTransfersTotal',
            'lastClosure',
        ));
    }

    public function addEntry(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'in:cash,card'],
            'amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        $entry = CashRegisterEntry::query()->create([
            'user_id' => $request->user()->id,
            'type' => $validated['type'],
            'amount' => $validated['amount'],
        ]);

        $totals = $this->openTotals($request->user()->id);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'تمت إضافة القيمة بنجاح.',
                'entry' => [
                    'id' => $entry->id,
                    'type' => $entry->type,
                    'amount' => number_format((float) $entry->amount, 2, '.', ''),
                    'created_at' => $entry->created_at?->format('Y-m-d H:i:s'),
                ],
                'open_cash_total' => number_format($totals['cash'], 2, '.', ''),
                'open_card_total' => number_format($totals['card'], 2, '.', ''),
            ]);
        }

        return back()->with('status', 'تمت إضافة القيمة بنجاح.');
    }

    public function closeAccount(Request $request): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        $payload = DB::transaction(function () use ($user) {
            $openEntries = CashRegisterEntry::query()
                ->forUser($user->id)
                ->open()
                ->lockForUpdate()
                ->get();

            $lastClosure = CashRegisterClosure::query()
                ->where('user_id', $user->id)
                ->orderByDesc('closed_at')
                ->lockForUpdate()
                ->first();

            $totalCash = (float) $openEntries->where('type', 'cash')->sum('amount');
            $totalCard = (float) $openEntries->where('type', 'card')->sum('amount');
            $totalTransfers = $this->transfersSinceLastClosure($user->id, $lastClosure?->closed_at);
            $grandTotal = $totalCash + $totalCard + $totalTransfers;

            $closure = CashRegisterClosure::query()->create([
                'user_id' => $user->id,
                'total_cash' => $totalCash,
                'total_card' => $totalCard,
                'total_transfers' => $totalTransfers,
                'grand_total' => $grandTotal,
                'closed_at' => now(),
            ]);

            CashRegisterEntry::query()
                ->forUser($user->id)
                ->open()
                ->update(['closed_in_id' => $closure->id]);

            return [
                'id' => $closure->id,
                'staff_name' => $user->name,
                'closed_at' => $closure->closed_at?->format('Y-m-d H:i:s'),
                'total_cash' => number_format((float) $closure->total_cash, 2, '.', ''),
                'total_card' => number_format((float) $closure->total_card, 2, '.', ''),
                'total_transfers' => number_format((float) $closure->total_transfers, 2, '.', ''),
                'grand_total' => number_format((float) $closure->grand_total, 2, '.', ''),
            ];
        });

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'تم إغلاق الحساب وتوثيقه بنجاح.',
                'closure' => $payload,
            ]);
        }

        return back()->with('status', 'تم إغلاق الحساب وتوثيقه بنجاح.');
    }

    /**
     * @return array{cash: float, card: float}
     */
    private function openTotals(int $userId): array
    {
        $open = CashRegisterEntry::query()
            ->forUser($userId)
            ->open()
            ->get();

        return [
            'cash' => (float) $open->where('type', 'cash')->sum('amount'),
            'card' => (float) $open->where('type', 'card')->sum('amount'),
        ];
    }

    private function transfersSinceLastClosure(int $userId, mixed $since): float
    {
        $query = Payment::query()
            ->where('processed_by', $userId)
            ->whereIn('gateway', ['bank_transfer', 'lypay', 'onepay']);

        if ($since) {
            $query->where('paid_at', '>', $since);
        }

        return (float) $query->sum('amount');
    }
}
