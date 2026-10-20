<?php

namespace App\Http\Controllers\Api\V1;

use App\Contracts\SubscriberProviderInterface;
use App\Exceptions\Subscriber\SubscriberNotFoundException;
use App\Exceptions\Subscriber\PackageChangeFailedException;
use App\Exceptions\Subscriber\RechargeFailedException;
use App\Exceptions\Subscriber\SubscriberProviderUnavailableException;
use App\Exceptions\Subscriber\SubscriberSuspendedException;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SubscriberApiController extends Controller
{
    public function __construct(
        private readonly SubscriberProviderInterface $subscriberProvider,
    ) {}

    public function show(string $contractNumber): JsonResponse
    {
        try {
            $data = $this->subscriberProvider->findSubscriberByContract($contractNumber);

            return $this->successResponse($data);
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    public function profile(Request $request): JsonResponse
    {
        $contractNumber = $this->resolveContractNumber($request);

        return $this->show($contractNumber);
    }

    public function subscription(?string $contractNumber = null, Request $request = null): JsonResponse
    {
        $contract = $contractNumber ?: $this->resolveContractNumber($request ?? request());

        try {
            $data = $this->subscriberProvider->getSubscription($contract);

            return $this->successResponse($data);
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    public function usage(?string $contractNumber = null, Request $request = null): JsonResponse
    {
        $contract = $contractNumber ?: $this->resolveContractNumber($request ?? request());

        try {
            $data = $this->subscriberProvider->getUsage($contract);

            return $this->successResponse($data);
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    public function borrowing(?string $contractNumber = null, Request $request = null): JsonResponse
    {
        $contract = $contractNumber ?: $this->resolveContractNumber($request ?? request());

        try {
            $data = $this->subscriberProvider->getBorrowingStatus($contract);

            return $this->successResponse($data);
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    public function recharge(?string $contractNumber = null, Request $request = null): JsonResponse
    {
        $req = $request ?? request();
        $contract = $contractNumber ?: $this->resolveContractNumber($req);

        $validated = $req->validate([
            'package_id' => ['required', 'string'],
        ], [
            'package_id.required' => 'معرّف الباقة مطلوب للشحن.',
        ]);

        $idempotencyKey = $req->header('X-Idempotency-Key');

        try {
            $package = $this->subscriberProvider->listAvailablePackages()[$validated['package_id']] ?? null;
            if (! is_array($package) || ! is_numeric($package['price'] ?? null)) {
                throw new RechargeFailedException('Selected package is unavailable.');
            }

            // Collect ledger details before charging so a later lookup failure cannot
            // leave a successful provider recharge without a local payment record.
            $subscriber = $this->subscriberProvider->findSubscriberByContract($contract);
            $result = $this->subscriberProvider->recharge($contract, $validated['package_id'], $idempotencyKey);
            if (! $result->success) {
                throw new RechargeFailedException('Subscriber provider reported an unsuccessful recharge.');
            }

            $reference = $result->transactionId;
            if (! is_string($reference) || $reference === '' || strlen($reference) > 255) {
                $reference = $idempotencyKey
                    ? 'API-' . Str::upper(substr(hash('sha256', $contract . '|' . $validated['package_id'] . '|' . $idempotencyKey), 0, 32))
                    : 'API-' . Str::uuid();
            }

            $actor = $req->user();
            $subscriberUser = $actor?->type === 'subscriber'
                ? $actor
                : User::query()
                    ->where('contract_number', $contract)
                    ->orWhere('username', $contract)
                    ->first();

            Payment::firstOrCreate(
                ['reference' => $reference],
                [
                    'user_id' => $subscriberUser?->id,
                    'contract_number' => $contract,
                    'subscriber_name' => $subscriber->name,
                    'gateway' => 'api',
                    'package_id' => $validated['package_id'],
                    'amount' => $package['price'],
                    'status' => 'completed',
                    'notes' => 'Recharge completed via subscriber API.',
                    'processed_by' => $actor && $actor->type !== 'subscriber' ? $actor->id : null,
                    'paid_at' => now(),
                ],
            );

            return $this->successResponse($result);
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    public function changePackage(?string $contractNumber = null, Request $request = null): JsonResponse
    {
        $req = $request ?? request();
        $contract = $contractNumber ?: $this->resolveContractNumber($req);

        $validated = $req->validate([
            'package_id' => ['required', 'string'],
        ], [
            'package_id.required' => 'معرّف الباقة الجديد مطلوب.',
        ]);

        $idempotencyKey = $req->header('X-Idempotency-Key');

        try {
            $result = $this->subscriberProvider->changePackage($contract, $validated['package_id'], $idempotencyKey);

            return $this->successResponse($result);
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    public function activateBorrowing(?string $contractNumber = null, Request $request = null): JsonResponse
    {
        $req = $request ?? request();
        $contract = $contractNumber ?: $this->resolveContractNumber($req);

        try {
            $result = $this->subscriberProvider->activateEmergencyBorrowing($contract);

            return $this->successResponse($result);
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    private function successResponse(mixed $data): JsonResponse
    {
        $payloadData = (is_object($data) && method_exists($data, 'toArray')) ? $data->toArray() : $data;

        return response()->json([
            'success' => true,
            'status' => 'success',
            'data' => $payloadData,
        ], 200);
    }

    private function resolveContractNumber(Request $request): string
    {
        $user = $request->user();

        if ($user && ! empty($user->contract_number)) {
            return (string) $user->contract_number;
        }

        if ($user && ! empty($user->username) && str_starts_with($user->username, 'TEST-')) {
            return (string) $user->username;
        }

        abort(403, 'No subscriber contract is linked to this account.');
    }

    private function handleException(\Throwable $e): JsonResponse
    {
        $statusCode = match (true) {
            $e instanceof SubscriberNotFoundException => 404,
            $e instanceof SubscriberSuspendedException => 403,
            $e instanceof SubscriberProviderUnavailableException => str_contains(strtolower($e->getMessage()), 'timed out') ? 504 : 503,
            $e instanceof RechargeFailedException, $e instanceof PackageChangeFailedException => 422,
            default => 500,
        };

        $message = match (true) {
            $e instanceof SubscriberNotFoundException => 'Subscriber not found.',
            $e instanceof SubscriberSuspendedException => 'Subscriber account is suspended.',
            $e instanceof SubscriberProviderUnavailableException => 'Subscriber service is temporarily unavailable.',
            $e instanceof RechargeFailedException => 'Recharge could not be completed.',
            $e instanceof PackageChangeFailedException => 'Package change could not be completed.',
            default => 'An unexpected error occurred.',
        };

        Log::error('Subscriber API request failed.', ['exception' => $e]);

        return response()->json([
            'success' => false,
            'status' => 'error',
            'message' => $message,
        ], $statusCode);
    }
}
