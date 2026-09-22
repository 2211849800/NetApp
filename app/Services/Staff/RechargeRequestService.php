<?php

namespace App\Services\Staff;

use App\Contracts\SubscriberProviderInterface;
use App\Exceptions\Subscriber\RechargeFailedException;
use App\Exceptions\Subscriber\SubscriberNotFoundException;
use App\Exceptions\Subscriber\SubscriberProviderUnavailableException;
use App\Exceptions\Subscriber\SubscriberSuspendedException;
use App\Models\Payment;
use App\Models\RechargeRequest;
use App\Models\User;
use App\Services\Audit\AuditLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class RechargeRequestService
{
    public function __construct(
        private readonly SubscriberProviderInterface $subscriberProvider,
        private readonly AuditLogService $auditLog,
    ) {}

    public function approve(RechargeRequest $request, User $actor, ?string $notes = null): RechargeRequest
    {
        if (! $request->isPending() || $request->processed_by !== null) {
            $processorName = $request->processor?->name ?? 'موظف آخر';
            throw new RuntimeException("عفواً، تم اعتماد وإضافة هذا التحويل مسبقاً لرصيد الموظف [{$processorName}] ولا يمكن معالجتها مرة أخرى حمايةً من التزوير والتكرار.");
        }


        if (! $request->contract_number || ! $request->package_id) {
            throw new RuntimeException('بيانات العقد أو الباقة ناقصة في الطلب.');
        }

        return DB::transaction(function () use ($request, $actor, $notes) {
            $result = $this->subscriberProvider->recharge(
                $request->contract_number,
                $request->package_id,
                'recharge-req-' . $request->id,
            );

            $old = $request->only(['status', 'notes']);

            $request->update([
                'status' => 'approved',
                'notes' => $notes ?: $request->notes,
                'processed_by' => $actor->id,
                'processed_at' => now(),
            ]);

            Payment::create([
                'user_id' => $request->user_id,
                'recharge_request_id' => $request->id,
                'contract_number' => $request->contract_number,
                'subscriber_name' => $request->subscriber_name,
                'gateway' => $request->payment_method ?: 'cash',
                'amount' => $request->amount ?? 0,
                'status' => 'completed',
                'reference' => $result->transactionId ?: ('PAY-' . Str::upper(Str::random(10))),
                'notes' => $notes,
                'processed_by' => $actor->id,
                'paid_at' => now(),
            ]);

            $this->auditLog->log('RECHARGE_REQUEST_APPROVED', 'RechargeRequest', $request->id, $old, [
                'status' => 'approved',
                'transaction_id' => $result->transactionId,
                'notes' => $notes,
            ], actor: $actor);

            return $request->fresh(['processor', 'user']);
        });
    }

    public function reject(RechargeRequest $request, User $actor, string $notes): RechargeRequest
    {
        if (! $request->isPending()) {
            throw new RuntimeException('لا يمكن معالجة طلب غير معلّق.');
        }

        return DB::transaction(function () use ($request, $actor, $notes) {
            $old = $request->only(['status', 'notes']);

            $request->update([
                'status' => 'rejected',
                'notes' => $notes,
                'processed_by' => $actor->id,
                'processed_at' => now(),
            ]);

            $this->auditLog->log('RECHARGE_REQUEST_REJECTED', 'RechargeRequest', $request->id, $old, [
                'status' => 'rejected',
                'notes' => $notes,
            ], actor: $actor);

            return $request->fresh(['processor', 'user']);
        });
    }

    public function actionErrorMessage(\Throwable $e): string
    {
        return match (true) {
            $e instanceof SubscriberNotFoundException,
            $e instanceof SubscriberSuspendedException,
            $e instanceof RechargeFailedException,
            $e instanceof SubscriberProviderUnavailableException,
            $e instanceof RuntimeException => $e->getMessage(),
            default => 'تعذر معالجة طلب الشحن.',
        };
    }
}
