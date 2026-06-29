<?php

namespace App\Http\Controllers;

use App\Services\ArroPayMayaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ArroPayMayaController extends Controller
{
    protected ArroPayMayaService $mayaService;

    public function __construct(ArroPayMayaService $mayaService)
    {
        $this->mayaService = $mayaService;
    }

    public function create(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|string|max:191',
            'amount' => 'required|numeric|min:0.01',
            'email' => 'required|email',
            'firstname' => 'required|string|max:100',
            'lastname' => 'required|string|max:100',
            'redirectSuccess' => 'required|url',
            'redirectFailure' => 'required|url',
            'redirectCancel' => 'required|url',
            'prefer_checkout' => 'sometimes|boolean',
        ]);

        $preferCheckout = !array_key_exists('prefer_checkout', $validated) || (bool) $validated['prefer_checkout'];
        $result = $this->mayaService->createPayment($validated, $preferCheckout);

        if (!$result['success']) {
            return $this->errorResponseByCode($result['status_code'], $result['message'], $result['errors']);
        }

        if (empty($result['payment_id']) || empty($result['redirect_url'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid gateway response. Missing payment reference or redirect URL.',
            ], 502);
        }

        $this->upsertTransaction(
            (string) $validated['order_id'],
            (string) $result['payment_id'],
            (float) $validated['amount'],
            'PENDING'
        );

        return response()->json([
            'success' => true,
            'payment_id' => $result['payment_id'],
            'redirect_url' => $result['redirect_url'],
            'status' => 'PENDING',
            'gateway' => 'arropay_maya',
            'raw' => $result['data'],
        ]);
    }

    public function check(Request $request)
    {
        $validated = $request->validate([
            'refno' => 'required|string|max:191',
        ]);

        $result = $this->mayaService->checkPayment($validated['refno']);
        if (!$result['success']) {
            return $this->errorResponseByCode($result['status_code'], $result['message'], $result['errors']);
        }

        $status = $result['status'];
        if (in_array($status, ['SUCCESS', 'FAILED', 'CANCELLED'], true)) {
            $this->updateTransactionStatus((string) $validated['refno'], $status);
        }

        return response()->json([
            'success' => true,
            'refno' => $validated['refno'],
            'status' => $status,
            'raw' => $result['data'],
        ]);
    }

    protected function errorResponseByCode(int $statusCode, string $message, $errors)
    {
        $statusMap = [401 => 401, 404 => 404, 422 => 422];
        $responseCode = $statusMap[$statusCode] ?? 502;

        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $responseCode);
    }

    protected function upsertTransaction(string $orderId, string $paymentId, float $amount, string $status): void
    {
        $table = $this->resolveTransactionTable();
        if (!$table) {
            return;
        }

        try {
            $payload = [
                'order_id' => $orderId,
                'payment_id' => $paymentId,
                'amount' => $amount,
                'status' => $status,
                'gateway' => 'arropay_maya',
                'updated_at' => now(),
            ];

            $existing = DB::table($table)->where('payment_id', $paymentId)->first();
            if ($existing) {
                DB::table($table)->where('payment_id', $paymentId)->update($payload);
                return;
            }

            $payload['created_at'] = now();
            DB::table($table)->insert($payload);
        } catch (\Throwable $e) {
            Log::warning('Unable to persist Maya transaction', ['error' => $e->getMessage()]);
        }
    }

    protected function updateTransactionStatus(string $paymentId, string $status): void
    {
        $table = $this->resolveTransactionTable();
        if (!$table) {
            return;
        }

        try {
            DB::table($table)->where('payment_id', $paymentId)->update([
                'status' => $status,
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Unable to update Maya transaction status', ['error' => $e->getMessage()]);
        }
    }

    protected function resolveTransactionTable(): ?string
    {
        foreach (['transaction', 'transactions'] as $table) {
            try {
                if (DB::getSchemaBuilder()->hasTable($table)) {
                    return $table;
                }
            } catch (\Throwable $e) {
                Log::warning('Unable to resolve transaction table', ['error' => $e->getMessage()]);
                return null;
            }
        }

        Log::warning('No transaction table found for ArroPay Maya integration.');
        return null;
    }
}
