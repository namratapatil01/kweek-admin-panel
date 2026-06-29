<?php

namespace App\Http\Controllers;
<<<<<<< HEAD
use Illuminate\Support\Facades\Auth;
use App\Models\VendorUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Google\Client as Google_Client;

class BookTableController extends Controller
{

=======

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class BookTableController extends Controller
{
>>>>>>> 4c9a071090dc3b20faed875c7d70567ba65ae18f
    public function __construct()
    {
        $this->middleware('auth');
    }

<<<<<<< HEAD
    public function index($id='')
    {

        return view("bookTable.index")->with('id',$id);
=======
    public function index($id = '')
    {
        return view('bookTable.index')->with('id', $id);
>>>>>>> 4c9a071090dc3b20faed875c7d70567ba65ae18f
    }

    public function edit($id)
    {
        return view('bookTable.edit')->with('id', $id);
    }

    public function sendnotification(Request $request)
    {
<<<<<<< HEAD

        if(Storage::disk('local')->has('firebase/credentials.json')){
            
            $client= new Google_Client();
            $client->setAuthConfig(storage_path('app/firebase/credentials.json'));
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
            $client->refreshTokenWithAssertion();
            $client_token = $client->getAccessToken();
            $access_token = $client_token['access_token'];

            $fcm_token = $request->fcm;
            
            if(!empty($access_token) && !empty($fcm_token)){

                $projectId = env('FIREBASE_PROJECT_ID');
                $url = 'https://fcm.googleapis.com/v1/projects/'.$projectId.'/messages:send';

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

                $headers = array(
                    'Content-Type: application/json',
                    'Authorization: Bearer '.$access_token
                );

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                
                $result = curl_exec($ch);
                if ($result === FALSE) {
                    die('FCM Send Error: ' . curl_error($ch));
                }
                curl_close($ch);
                $result=json_decode($result);

                $response = array();
                $response['success'] = true;
                $response['message'] = 'Notification successfully sent.';
                $response['result'] = $result;

            }else{
                $response = array();
                $response['success'] = false;
                $response['message'] = 'Missing sender id or token to send notification.';
            }

        }else{
            $response = array();
            $response['success'] = false;
            $response['message'] = 'Firebase credentials file not found.';
        }
       
        return response()->json($response);
    }
}


=======
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
>>>>>>> 4c9a071090dc3b20faed875c7d70567ba65ae18f
