<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ProvidesMySqlCrud;
use App\Services\DocumentStoreService;
use Google\Client as Google_Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class OrderController extends Controller
{
    use ProvidesMySqlCrud;

    public function __construct()
    {
        $this->middleware('auth');
    }

    protected function moduleSlug(): string
    {
        return 'vendor-orders';
    }

    public function index($id = ''): View
    {
        $service_type = request()->cookie('service_type', $_COOKIE['service_type'] ?? '');

        return view('orders.index', [
            'id' => $id,
            'service_type' => $service_type,
        ]);
    }

    public function edit(DocumentStoreService $store, ...$params): View
    {
        $id = (string) end($params);

        return view('orders.edit', [
            'id' => $id,
            'oid' => '',
            'order' => $store->getDocument('vendor_orders', $id),
        ]);
    }

    public function review($oid, $id = '', DocumentStoreService $store): View
    {
        $orderId = $oid !== '' ? $oid : $id;

        return view('orders.edit', [
            'oid' => $oid,
            'id' => $id,
            'order' => $orderId !== '' ? $store->getDocument('vendor_orders', $orderId) : null,
        ]);
    }

    public function sendNotification(Request $request)
    {
        if (Storage::disk('local')->has('firebase/credentials.json')) {
            $client = new Google_Client();
            $client->setAuthConfig(storage_path('app/firebase/credentials.json'));
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
            $client->refreshTokenWithAssertion();
            $client_token = $client->getAccessToken();
            $access_token = $client_token['access_token'];

            $fcm_token = $request->fcm;

            if (! empty($access_token) && ! empty($fcm_token)) {
                $projectId = env('FIREBASE_PROJECT_ID');
                $url = 'https://fcm.googleapis.com/v1/projects/' . $projectId . '/messages:send';

                $data = [
                    'message' => [
                        'notification' => [
                            'title' => $request->subject,
                            'body' => $request->message,
                        ],
                        'data' => [
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                            'id' => '1',
                            'status' => 'done',
                        ],
                        'token' => $fcm_token,
                    ],
                ];

                $headers = [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $access_token,
                ];

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

                $result = curl_exec($ch);
                if ($result === false) {
                    die('FCM Send Error: ' . curl_error($ch));
                }
                curl_close($ch);
                $result = json_decode($result);

                $response = [];
                $response['success'] = true;
                $response['message'] = 'Notification successfully sent.';
                $response['result'] = $result;
            } else {
                $response = [];
                $response['success'] = false;
                $response['message'] = 'Missing sender id or token to send notification.';
            }
        } else {
            $response = [];
            $response['success'] = false;
            $response['message'] = 'Firebase credentials file not found.';
        }

        return response()->json($response);
    }

    public function orderprint($id = ''): View
    {
        return view('orders.print', ['id' => $id]);
    }

    public function ownerOrderList($id = ''): View
    {
        return view('orders.owner_index', ['id' => $id]);
    }
}
