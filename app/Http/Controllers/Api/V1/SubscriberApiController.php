<?php

namespace App\Http\Controllers\Api\V1;

use App\Contracts\SubscriberProviderInterface;
use App\Exceptions\Subscriber\SubscriberNotFoundException;
use App\Exceptions\Subscriber\SubscriberProviderUnavailableException;
use App\Exceptions\Subscriber\SubscriberSuspendedException;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
            $result = $this->subscriberProvider->recharge($contract, $validated['package_id'], $idempotencyKey);

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

        return (string) ($request->input('contract_number') ?? $request->route('contractNumber') ?? 'TEST-100001');
    }

    private function handleException(\Throwable $e): JsonResponse
    {
        $statusCode = match (true) {
            $e instanceof SubscriberNotFoundException => 404,
            $e instanceof SubscriberSuspendedException => 403,
            $e instanceof SubscriberProviderUnavailableException => str_contains(strtolower($e->getMessage()), 'timed out') ? 504 : 503,
            default => 422,
        };

        return response()->json([
            'success' => false,
            'status' => 'error',
            'message' => $e->getMessage(),
        ], $statusCode);
    }
}
