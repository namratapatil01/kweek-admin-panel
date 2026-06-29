<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class BookTableController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index($id = '')
    {
        return view('bookTable.index')->with('id', $id);
    }

    public function edit($id)
    {
        return view('bookTable.edit')->with('id', $id);
    }

    public function sendnotification(Request $request)
    {
        $fcmToken = (string) $request->input('fcm', '');
        $subject = (string) $request->input('subject', '');
        $message = (string) $request->input('message', '');

        if ($fcmToken === '') {
            return response()->json([
                'success' => false,
                'message' => 'Missing FCM token to send notification.',
            ]);
        }

        $serverKey = (string) env('FCM_SERVER_KEY', '');
        if ($serverKey !== '') {
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $serverKey,
                'Content-Type' => 'application/json',
            ])->post('https://fcm.googleapis.com/fcm/send', [
                'to' => $fcmToken,
                'notification' => [
                    'title' => $subject,
                    'body' => $message,
                ],
                'data' => [
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    'id' => '1',
                    'status' => 'done',
                ],
            ]);

            return response()->json([
                'success' => $response->successful(),
                'message' => $response->successful()
                    ? 'Notification successfully sent.'
                    : 'Unable to send notification via FCM.',
                'result' => $response->json(),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'FCM is not configured. Set FCM_SERVER_KEY in .env.',
        ]);
    }
}
