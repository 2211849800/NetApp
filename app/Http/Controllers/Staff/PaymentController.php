<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $payments = Payment::query()
            ->with(['user', 'processor'])
            ->when($request->filled('gateway'), fn ($q) => $q->where('gateway', $request->input('gateway')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('from_date'), fn ($q) => $q->whereDate('created_at', '>=', $request->input('from_date')))
            ->when($request->filled('to_date'), fn ($q) => $q->whereDate('created_at', '<=', $request->input('to_date')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->input('q');
                $q->where(function ($inner) use ($term) {
                    $inner->where('reference', 'like', "%{$term}%")
                        ->orWhere('contract_number', 'like', "%{$term}%")
                        ->orWhere('subscriber_name', 'like', "%{$term}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('staff.payments.index', [
            'payments' => $payments,
            'gateways' => Payment::GATEWAYS,
            'statuses' => Payment::STATUSES,
            'filters' => $request->only(['gateway', 'status', 'from_date', 'to_date', 'q']),
        ]);
    }
}
