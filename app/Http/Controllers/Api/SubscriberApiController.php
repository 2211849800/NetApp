<?php

namespace App\Http\Controllers\Api;

use App\Contracts\SubscriberProviderInterface;
use App\Exceptions\Subscriber\PackageChangeFailedException;
use App\Exceptions\Subscriber\RechargeFailedException;
use App\Exceptions\Subscriber\SubscriberNotFoundException;
use App\Exceptions\Subscriber\SubscriberProviderUnavailableException;
use App\Exceptions\Subscriber\SubscriberSuspendedException;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Internal API controller for subscriber operations.
 *
 * Delegates to whichever SubscriberProviderInterface implementation
 * is currently bound (Mock or real ADV).
 */
class SubscriberApiController extends Controller
{
    public function __construct(
        private readonly SubscriberProviderInterface $subscriberProvider,
    ) {}

    /**
     * GET /api/v1/subscribers/{contractNumber}
     */
    public function show(string $contractNumber): JsonResponse
    {
        try {
            $subscriber = $this->subscriberProvider->findSubscriberByContract($contractNumber);

            return response()->json([
                'success' => true,
                'data' => $subscriber->toArray(),
            ]);
        } catch (SubscriberNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => $this->safeErrorMessage($e),
            ], 404);
        } catch (SubscriberProviderUnavailableException $e) {
            return $this->handleProviderUnavailable($e);
        }
    }

    /**
     * GET /api/v1/subscribers/{contractNumber}/subscription
     */
    public function subscription(string $contractNumber): JsonResponse
    {
        try {
            $subscription = $this->subscriberProvider->getSubscription($contractNumber);

            return response()->json([
                'success' => true,
                'data' => $subscription->toArray(),
            ]);
        } catch (SubscriberNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => $this->safeErrorMessage($e),
            ], 404);
        } catch (SubscriberProviderUnavailableException $e) {
            return $this->handleProviderUnavailable($e);
        }
    }

    /**
     * GET /api/v1/subscribers/{contractNumber}/usage
     */
    public function usage(string $contractNumber): JsonResponse
    {
        try {
            $usage = $this->subscriberProvider->getUsage($contractNumber);

            return response()->json([
                'success' => true,
                'data' => $usage->toArray(),
            ]);
        } catch (SubscriberNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => $this->safeErrorMessage($e),
            ], 404);
        } catch (SubscriberProviderUnavailableException $e) {
            return $this->handleProviderUnavailable($e);
        }
    }

    /**
     * GET /api/v1/subscribers/{contractNumber}/borrowing
     */
    public function borrowing(string $contractNumber): JsonResponse
    {
        try {
            $borrowing = $this->subscriberProvider->getBorrowingStatus($contractNumber);

            return response()->json([
                'success' => true,
                'data' => $borrowing->toArray(),
            ]);
        } catch (SubscriberNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => $this->safeErrorMessage($e),
            ], 404);
        } catch (SubscriberProviderUnavailableException $e) {
            return $this->handleProviderUnavailable($e);
        }
    }

    /**
     * POST /api/v1/subscribers/{contractNumber}/recharge
     */
    public function recharge(Request $request, string $contractNumber): JsonResponse
    {
        $request->validate([
            'package_id' => ['required', 'string'],
        ]);

        try {
            $result = $this->subscriberProvider->recharge(
                $contractNumber,
                $request->input('package_id'),
                $request->header('X-Idempotency-Key'),
            );

            return response()->json([
                'success' => true,
                'data' => $result->toArray(),
            ]);
        } catch (SubscriberNotFoundException $e) {
            return response()->json(['success' => false, 'error' => $this->safeErrorMessage($e)], 404);
        } catch (SubscriberSuspendedException $e) {
            return response()->json(['success' => false, 'error' => $this->safeErrorMessage($e)], 403);
        } catch (RechargeFailedException $e) {
            return response()->json(['success' => false, 'error' => $this->safeErrorMessage($e)], 422);
        } catch (SubscriberProviderUnavailableException $e) {
            return $this->handleProviderUnavailable($e);
        }
    }

    /**
     * POST /api/v1/subscribers/{contractNumber}/change-package
     */
    public function changePackage(Request $request, string $contractNumber): JsonResponse
    {
        $request->validate([
            'package_id' => ['required', 'string'],
        ]);

        try {
            $result = $this->subscriberProvider->changePackage(
                $contractNumber,
                $request->input('package_id'),
                $request->header('X-Idempotency-Key'),
            );

            return response()->json([
                'success' => true,
                'data' => $result->toArray(),
            ]);
        } catch (SubscriberNotFoundException $e) {
            return response()->json(['success' => false, 'error' => $this->safeErrorMessage($e)], 404);
        } catch (SubscriberSuspendedException $e) {
            return response()->json(['success' => false, 'error' => $this->safeErrorMessage($e)], 403);
        } catch (PackageChangeFailedException $e) {
            return response()->json(['success' => false, 'error' => $this->safeErrorMessage($e)], 422);
        } catch (SubscriberProviderUnavailableException $e) {
            return $this->handleProviderUnavailable($e);
        }
    }

    /**
     * Handle external provider availability errors with appropriate HTTP status codes.
     */
    private function handleProviderUnavailable(SubscriberProviderUnavailableException $e): JsonResponse
    {
        $status = str_contains(strtolower($e->getMessage()), 'timed out') ? 504 : 503;
        Log::warning('Subscriber provider is unavailable.', ['exception' => $e]);

        return response()->json([
            'success' => false,
            'error' => 'Subscriber service is temporarily unavailable.',
        ], $status);
    }

    private function safeErrorMessage(\Throwable $e): string
    {
        Log::warning('Subscriber API operation failed.', ['exception' => $e]);

        return match (true) {
            $e instanceof SubscriberNotFoundException => 'Subscriber not found.',
            $e instanceof SubscriberSuspendedException => 'Subscriber account is suspended.',
            $e instanceof RechargeFailedException => 'Recharge could not be completed.',
            $e instanceof PackageChangeFailedException => 'Package change could not be completed.',
            default => 'Subscriber operation could not be completed.',
        };
    }
}

