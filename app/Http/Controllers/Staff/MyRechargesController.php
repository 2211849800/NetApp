<?php

namespace App\Http\Controllers\Staff;

use App\Contracts\SubscriberProviderInterface;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MyRechargesController extends Controller
{
    public function __construct(private readonly SubscriberProviderInterface $subscriberProvider) {}

    public function index(Request $request): View
    {
        $query = trim((string) $request->query('q', ''));
        $payments = Payment::query()
            ->where('processed_by', $request->user()->id)
            ->where('status', 'completed')
            ->whereNotNull('contract_number')
            ->when($query !== '', function ($builder) use ($query) {
                $builder->where(function ($filter) use ($query) {
                    $filter->where('contract_number', 'like', "%{$query}%")
                        ->orWhere('subscriber_name', 'like', "%{$query}%")
                        ->orWhere('reference', 'like', "%{$query}%");
                });
            })
            ->orderByDesc('paid_at')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('staff.my-recharges.index', [
            'payments' => $payments,
            'query' => $query,
            'packages' => $this->subscriberProvider->listAvailablePackages(),
        ]);
    }
}
