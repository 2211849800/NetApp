<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Payment\PaymentWebhookProcessor;
use App\Services\Payment\WebhookSignatureService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function __construct(
        private readonly WebhookSignatureService $signatureService,
        private readonly PaymentWebhookProcessor $processor,
    ) {}

    public function handleLypay(Request $request): JsonResponse
    {
        return $this->processGatewayWebhook('lypay', $request, 'X-Lypay-Signature');
    }

    public function handleOnePay(Request $request): JsonResponse
    {
        return $this->processGatewayWebhook('onepay', $request, 'X-OnePay-Signature');
    }

    public function simulate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'gateway' => ['required', 'string', 'in:lypay,onepay'],
            'contract_number' => ['required', 'string'],
            'amount' => ['required', 'numeric', 'min:1'],
            'package_id' => ['nullable', 'string'],
        ], [
            'contract_number.required' => 'رقم العقد مطلوب لمحاكاة الدفع.',
            'amount.required' => 'المبلغ مطلوب.',
        ]);

        try {
            $result = $this->processor->process($validated['gateway'], [
                'reference' => strtoupper($validated['gateway']).'-SIM-'.rand(100000, 999999),
                'contract_number' => $validated['contract_number'],
                'amount' => $validated['amount'],
                'package_id' => $validated['package_id'] ?? '1',
                'status' => 'SUCCESS',
            ]);

            return back()->with('status', '[محاكي الدفع] '.$result['message']);
        } catch (\Throwable $e) {
            return back()->with('error', '[محاكي الدفع] '.$e->getMessage());
        }
    }

    private function processGatewayWebhook(string $gateway, Request $request, string $signatureHeaderName): JsonResponse
    {
        $content = $request->getContent();
        $signature = $request->header($signatureHeaderName)
            ?? $request->header('X-Signature')
            ?? $request->input('signature');

        if (! $this->signatureService->verify($gateway, $content, $signature)) {
            return response()->json([
                'status' => 'error',
                'message' => 'التوقيع التشفيري غير صالح (Invalid Webhook Signature).',
            ], 401);
        }

        $payload = $request->all();

        if (empty($payload)) {
            $payload = json_decode($content, true) ?? [];
        }

        try {
            $result = $this->processor->process($gateway, $payload);

            return response()->json([
                'status' => 'success',
                'data' => $result,
            ], 200);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ غير متوقع أثناء معالجة إشعار الـ Webhook.',
            ], 500);
        }
    }
}
