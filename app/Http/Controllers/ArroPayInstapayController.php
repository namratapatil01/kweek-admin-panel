<?php

namespace App\Http\Controllers;

use App\Services\ArroPayInstapayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ArroPayInstapayController extends Controller
{
    protected ArroPayInstapayService $instapayService;

    public function __construct(ArroPayInstapayService $instapayService)
    {
        $this->instapayService = $instapayService;
    }

    public function process(Request $request)
    {
        $validated = $request->validate([
            'refno' => 'required|string|max:191',
            'amount' => 'required|numeric|min:0.01|max:50000',
            'callbackURL' => 'sometimes|nullable|url',
            'sender_account_name' => 'sometimes|string|max:191',
            'sender_account_number' => 'sometimes|string|max:191',
            'sender_mobile_number' => 'sometimes|string|max:50',
            'sender_email' => 'sometimes|email|max:191',
            'sender_address' => 'sometimes|string|max:255',
            'sender_barangay' => 'sometimes|string|max:191',
            'sender_city' => 'sometimes|string|max:191',
            'sender_zipcode' => 'sometimes|string|max:20',
        ]);

        $payload = array_merge($request->except(['_token']), $validated);
        $result = $this->instapayService->processDisbursement($payload);

        if (!$result['success']) {
            return $this->errorResponseByCode($result['status_code'], $result['message'], $result['errors']);
        }

        $status = $result['status'] ?? 'PENDING';
        $paymentId = (string) ($result['refno'] ?? $validated['refno']);

        $this->upsertTransaction(
            $paymentId,
            $paymentId,
            (float) $validated['amount'],
            $status
        );

        return response()->json([
            'success' => true,
            'refno' => $paymentId,
            'status' => $status,
            'gateway' => 'arropay_instapay',
            'raw' => $result['data'],
        ]);
    }

    public function check(Request $request)
    {
        $validated = $request->validate([
            'refno' => 'required|string|max:191',
        ]);

        $payload = array_merge($request->except(['_token']), $validated);
        $result = $this->instapayService->checkDisbursement($payload);

        if (!$result['success']) {
            return $this->errorResponseByCode($result['status_code'], $result['message'], $result['errors']);
        }

        $status = $result['status'] ?? 'PENDING';
        if (in_array($status, ['SUCCESS', 'FAILED', 'CANCELLED'], true)) {
            $this->updateTransactionStatus((string) $validated['refno'], $status);
        }

        return response()->json([
            'success' => true,
            'refno' => $validated['refno'],
            'status' => $status,
            'gateway' => 'arropay_instapay',
            'raw' => $result['data'],
        ]);
    }

    public function callback(Request $request)
    {
        $refno = (string) ($request->input('refno') ?? $request->input('reference') ?? '');
        $status = $this->instapayService->normalizeStatus(
            $request->input('status') ?? $request->input('paymentStatus') ?? $request->input('code')
        );

        if ($refno !== '' && in_array($status, ['SUCCESS', 'FAILED', 'CANCELLED'], true)) {
            $this->updateTransactionStatus($refno, $status);
        }

        return response()->json(['success' => true]);
    }

    protected function errorResponseByCode(int $statusCode, string $message, $errors)
    {
        $statusMap = [401 => 401, 403 => 403, 404 => 404, 422 => 422, 500 => 502];
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
                'gateway' => 'arropay_instapay',
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
            Log::warning('Unable to persist Instapay transaction', ['error' => $e->getMessage()]);
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
            Log::warning('Unable to update Instapay transaction status', ['error' => $e->getMessage()]);
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

        Log::warning('No transaction table found for ArroPay Instapay integration.');
        return null;
    }
}
