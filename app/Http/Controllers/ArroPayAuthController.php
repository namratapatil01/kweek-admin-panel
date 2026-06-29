<?php

namespace App\Http\Controllers;

use App\Services\ArroPayAuthService;
use Illuminate\Http\Request;

class ArroPayAuthController extends Controller
{
    protected ArroPayAuthService $authService;

    public function __construct(ArroPayAuthService $authService)
    {
        $this->middleware('auth');
        $this->authService = $authService;
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'baseUrl' => 'required|url|max:255',
            'apiKey' => 'required|string|max:255',
            'apiSecret' => 'required|string|max:255',
            'loginEndpoint' => 'nullable|string|max:255',
        ]);

        $result = $this->authService->login(
            $validated['baseUrl'],
            $validated['apiKey'],
            $validated['apiSecret'],
            $validated['loginEndpoint'] ?? null
        );

        if (!$result['success']) {
            $statusMap = [401 => 401, 403 => 403, 404 => 404, 422 => 422];
            $responseCode = $statusMap[$result['status_code']] ?? 502;

            return response()->json([
                'success' => false,
                'message' => $result['message'],
                'errors' => $result['errors'],
            ], $responseCode);
        }

        return response()->json([
            'success' => true,
            'token' => $result['token'],
            'expires_at' => $result['expires_at'],
            'login_url' => $result['login_url'],
            'raw' => $result['data'],
        ]);
    }
}
