<?php

namespace App\Services\Staff;

use App\Contracts\SubscriberProviderInterface;
use App\Enums\UserStatus;
use App\Exceptions\Subscriber\SubscriberProvisionFailedException;
use App\Models\SubscriptionRequest;
use App\Models\User;
use App\Services\Audit\AuditLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class SubscriptionRequestService
{
    public function __construct(
        private readonly SubscriberProviderInterface $subscriberProvider,
        private readonly AuditLogService $auditLog,
    ) {}

    /**
     * @return array{request: SubscriptionRequest, user: User, password: string, contract_number: string}
     */
    public function approve(SubscriptionRequest $request, User $actor, ?string $notes = null): array
    {
        if (! $request->isPending() && $request->status !== 'under_review') {
            throw new RuntimeException('لا يمكن قبول طلب غير معلّق.');
        }

        $packageId = $request->package_id ?: 'PKG-70';

        return DB::transaction(function () use ($request, $actor, $notes, $packageId) {
            $provisioned = $this->subscriberProvider->provisionSubscriber(
                $request->applicant_name,
                $request->phone,
                $packageId,
            );

            $password = Str::password(10);
            $username = $this->uniqueUsername($provisioned->contractNumber, $request->phone);

            $user = User::create([
                'name' => $request->applicant_name,
                'username' => $username,
                'contract_number' => $provisioned->contractNumber,
                'phone' => $request->phone,
                'email' => $request->email,
                'password' => $password,
                'type' => 'subscriber',
                'status' => UserStatus::Active,
                'is_active' => true,
            ]);

            $user->assignRole('subscriber');

            $old = $request->only(['status', 'user_id']);

            $request->update([
                'status' => 'approved',
                'user_id' => $user->id,
                'internal_notes' => $notes ?: $request->internal_notes,
                'processed_by' => $actor->id,
                'processed_at' => now(),
            ]);

            $this->auditLog->log('SUBSCRIPTION_REQUEST_APPROVED', 'SubscriptionRequest', $request->id, $old, [
                'status' => 'approved',
                'user_id' => $user->id,
                'contract_number' => $provisioned->contractNumber,
                'username' => $username,
            ], actor: $actor);

            return [
                'request' => $request->fresh(['processor', 'user']),
                'user' => $user,
                'password' => $password,
                'contract_number' => $provisioned->contractNumber,
            ];
        });
    }

    public function reject(SubscriptionRequest $request, User $actor, string $reason): SubscriptionRequest
    {
        if (! $request->isPending() && $request->status !== 'under_review') {
            throw new RuntimeException('لا يمكن رفض طلب غير معلّق.');
        }

        return DB::transaction(function () use ($request, $actor, $reason) {
            $old = $request->only(['status', 'internal_notes']);

            $request->update([
                'status' => 'rejected',
                'internal_notes' => $reason,
                'processed_by' => $actor->id,
                'processed_at' => now(),
            ]);

            $this->auditLog->log('SUBSCRIPTION_REQUEST_REJECTED', 'SubscriptionRequest', $request->id, $old, [
                'status' => 'rejected',
                'reason' => $reason,
            ], actor: $actor);

            return $request->fresh(['processor', 'user']);
        });
    }

    public function actionErrorMessage(\Throwable $e): string
    {
        return match (true) {
            $e instanceof SubscriberProvisionFailedException,
            $e instanceof RuntimeException => $e->getMessage(),
            default => 'تعذر معالجة طلب الاشتراك.',
        };
    }

    private function uniqueUsername(string $contractNumber, string $phone): string
    {
        $base = 'sub_' . strtolower(preg_replace('/[^A-Za-z0-9]/', '', $contractNumber) ?: $phone);
        $username = $base;
        $i = 1;

        while (User::where('username', $username)->exists()) {
            $username = $base . '_' . $i;
            $i++;
        }

        return $username;
    }
}
