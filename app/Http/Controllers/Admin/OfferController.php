<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RecordStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreOfferRequest;
use App\Http\Requests\Admin\UpdateOfferRequest;
use App\Models\Offer;
use App\Services\Audit\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OfferController extends Controller
{
    public function __construct(private readonly AuditLogService $auditLog) {}

    public function index(): View
    {
        $offers = Offer::orderByDesc('created_at')->get();

        return view('admin.offers.index', compact('offers'));
    }

    public function create(): View
    {
        return view('admin.offers.create');
    }

    public function store(StoreOfferRequest $request): RedirectResponse
    {
        $offer = Offer::create($request->validated());
        $this->auditLog->logModelChange('OFFER_CREATED', $offer);

        return redirect()->route('admin.offers.index')->with('status', 'تم إنشاء العرض بنجاح.');
    }

    public function show(Offer $offer): View
    {
        return view('admin.offers.show', compact('offer'));
    }

    public function edit(Offer $offer): View
    {
        return view('admin.offers.edit', compact('offer'));
    }

    public function update(UpdateOfferRequest $request, Offer $offer): RedirectResponse
    {
        $old = $offer->getAttributes();
        $offer->update($request->validated());
        $this->auditLog->log('OFFER_UPDATED', 'Offer', $offer->id, $old, $offer->getAttributes());

        return redirect()->route('admin.offers.index')->with('status', 'تم تحديث العرض بنجاح.');
    }

    public function toggleStatus(Offer $offer): RedirectResponse
    {
        $old = $offer->status?->value;
        $offer->update([
            'status' => $offer->status === RecordStatus::Active
                ? RecordStatus::Inactive
                : RecordStatus::Active,
        ]);

        $action = $offer->status === RecordStatus::Active ? 'OFFER_ACTIVATED' : 'OFFER_DEACTIVATED';
        $this->auditLog->log($action, 'Offer', $offer->id, ['status' => $old], ['status' => $offer->status->value]);

        return back()->with('status', 'تم تحديث حالة العرض.');
    }
}
