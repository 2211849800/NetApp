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
            // The route-bound instance may be stale; serialize approvals on the persisted row.
            $request = RechargeRequest::query()->whereKey($request->getKey())->lockForUpdate()->firstOrFail();
            if (! $request->isPending() || $request->processed_by !== null) {
                $processorName = $request->processor?->name ?? 'ظ…ظˆط¸ظپ ط¢ط®ط±';
                throw new RuntimeException("ط¹ظفظˆط§ظ‹طŒ طھظ… طاھط¹طھظ…ط§ط¯ ظˆط¥ط¶ط§ظپط© ظ‡ط°ط§ طاظ„طھط­ظˆظٹظ„ ظ…ط³ط¨ظ‚ط§ظ‹ ظ„ط±طµظٹط¯ ظaظ„ظ…ظˆط¸ظپ [{$processorName}] ظˆظ„ط§ ظٹظ…ظƒظ† ظ…ط¹ط§ظ„ط¬طھظ‡ط§ ظ…ط±ط© ط£ط®ط±ظ‰.");
            }
            if (! $request->contract_number || ! $request->package_id) {
                throw new RuntimeException('ط¨ظٹط§ظ†ط§طھ طaظ„ط¹ظ‚ط¯ ط£ظˆ طaظ„ط¨ط§ظ‚ط© ظ†ط§ظ‚طµط© ظپظٹ طaظ„ط·ظ„ط¨.');
            }

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
            $request = RechargeRequest::query()->whereKey($request->getKey())->lockForUpdate()->firstOrFail();
            if (! $request->isPending()) {
                throw new RuntimeException('ظ„ط§ ظٹظ…ظƒظ† ظ…ط¹ط§ظ„ط¬ط© ط·ظ„ط¨ ط؛ظٹط± ظ…ط¹ظ„ظ‘ظ‚.');
            }

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
