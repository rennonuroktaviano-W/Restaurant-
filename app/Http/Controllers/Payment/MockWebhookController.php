<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\PaymentGateway\GatewayManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class MockWebhookController extends Controller
{
    public function __construct(protected GatewayManager $gateways) {}

    public function handle(Request $request): JsonResponse
    {
        $payload = $request->json()->all();

        try {
            $payment = $this->gateways->driver('mock')->handleWebhook($payload);
        } catch (RuntimeException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'status' => 'ok',
            'payment_status' => $payment->fresh()->status,
        ]);
    }
}
