<?php

namespace App\Http\Controllers;

use App\Services\ArroPayDisbursementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ArroPayDisbursementController extends Controller
{
  protected ArroPayDisbursementService $disbursementService;

  public function __construct(ArroPayDisbursementService $disbursementService)
  {
    $this->disbursementService = $disbursementService;
  }

  public function banks(Request $request): JsonResponse
  {
    $validated = $request->validate([
      'channel' => ['required', 'string', Rule::in(['INSTAPAY', 'PESONET'])],
    ]);

    $result = $this->disbursementService->getBanks($validated['channel']);

    if (!$result['success']) {
      return $this->errorResponseByCode($result['status_code'], $result['message'], $result['errors'] ?? null);
    }

    return response()->json([
      'channel' => $result['channel'],
      'banks' => $result['banks'],
    ]);
  }

  public function initiateBankWithdraw(Request $request): JsonResponse
  {
    $request->merge([
      'fullName' => $request->input('fullName') ?? $request->input('full_name'),
      'accountNumber' => $request->input('accountNumber') ?? $request->input('account_number'),
      'bankCode' => $request->input('bankCode') ?? $request->input('bank_code'),
      'orderNumber' => $request->input('orderNumber') ?? $request->input('order_number'),
      'notifyUrl' => $request->input('notifyUrl') ?? $request->input('notify_url'),
    ]);

    $validated = $request->validate([
      'fullName' => 'required|string|max:191',
      'phone' => ['required', 'string', 'regex:/^\d{10}$|^\d{12}$|^\d{13}$/'],
      'accountNumber' => 'required|string|max:191',
      'bankCode' => 'required|string|max:191',
      'channel' => ['required', 'string', Rule::in(['INSTAPAY', 'PESONET'])],
      'amount' => 'required|numeric|min:1',
      'orderNumber' => 'sometimes|nullable|string|max:191',
      'notifyUrl' => 'sometimes|nullable|url|max:500',
    ]);

    $payload = array_filter([
      'fullName' => $validated['fullName'],
      'phone' => $validated['phone'],
      'accountNumber' => $validated['accountNumber'],
      'bankCode' => $validated['bankCode'],
      'channel' => $validated['channel'],
      'amount' => (float) $validated['amount'],
      'orderNumber' => $validated['orderNumber'] ?? null,
      'notifyUrl' => $validated['notifyUrl'] ?? null,
    ], static fn ($value) => $value !== null && $value !== '');

    $result = $this->disbursementService->initiateBankWithdraw($payload);

    if (!$result['success'] && ($result['status_code'] ?? 0) >= 400) {
      return $this->errorResponseByCode(
        (int) $result['status_code'],
        $result['message'],
        $result['errors'] ?? null,
        $this->withdrawalPayload($result)
      );
    }

    return response()->json($this->withdrawalPayload($result));
  }

  public function processBankWithdraw(Request $request): JsonResponse
  {
    $request->merge([
      'transactionId' => $request->input('transactionId') ?: $request->input('transaction_id'),
      'orderNumber' => $request->input('orderNumber') ?: $request->input('order_number'),
    ]);

    $validated = $request->validate([
      'otp' => 'required|string|max:20',
      'transactionId' => 'required_without:orderNumber|nullable|string|max:191',
      'orderNumber' => 'required_without:transactionId|nullable|string|max:191',
      'channel' => ['required_with:orderNumber', 'nullable', 'string', Rule::in(['INSTAPAY', 'PESONET'])],
    ]);

    $payload = array_filter([
      'otp' => $validated['otp'],
      'transactionId' => $validated['transactionId'] ?? null,
      'orderNumber' => $validated['orderNumber'] ?? null,
      'channel' => $validated['channel'] ?? null,
    ], static fn ($value) => $value !== null && $value !== '');

    $result = $this->disbursementService->processBankWithdraw($payload);

    if (!$result['success'] && ($result['status_code'] ?? 0) === 404) {
      return $this->errorResponseByCode(
        404,
        $result['message'],
        $result['errors'] ?? null,
        $this->withdrawalPayload($result)
      );
    }

    if (!$result['success'] && ($result['status_code'] ?? 0) >= 400) {
      return $this->errorResponseByCode(
        (int) $result['status_code'],
        $result['message'],
        $result['errors'] ?? null,
        $this->withdrawalPayload($result)
      );
    }

    return response()->json($this->withdrawalPayload($result));
  }

  protected function withdrawalPayload(array $result): array
  {
    return [
      'success' => (bool) ($result['success'] ?? false),
      'status' => (string) ($result['status'] ?? 'PENDING'),
      'message' => (string) ($result['message'] ?? ''),
      'channel' => (string) ($result['channel'] ?? ''),
      'order_number' => (string) ($result['order_number'] ?? ''),
      'provider_order_number' => $result['provider_order_number'] ?? null,
      'transaction_id' => $result['transaction_id'] ?? null,
    ];
  }

  protected function errorResponseByCode(int $statusCode, string $message, $errors, array $extra = []): JsonResponse
  {
    $statusMap = [401 => 401, 403 => 403, 404 => 404, 422 => 422, 500 => 502];
    $responseCode = $statusMap[$statusCode] ?? 502;

    return response()->json(array_merge([
      'success' => false,
      'message' => $message,
      'errors' => $errors,
    ], $extra), $responseCode);
  }
}
