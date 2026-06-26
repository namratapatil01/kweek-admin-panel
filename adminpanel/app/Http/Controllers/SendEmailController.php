<?php

namespace App\Http\Controllers;

use App\Mail\SetEmailData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Redirect;

class SendEmailController extends Controller
{
    public function __construct()
    {
    }


    private function replaceBrandNames($text)
    {
        $text = preg_replace('/nexa/i', 'KWEEK', $text);
        $text = preg_replace('/emart/i', 'KWEEK', $text);

        return $text;
    }

    function sendMail(Request $request)
    {
        try {
                    $data = $request->all();

        $subject = $this->replaceBrandNames($data['subject']);
        $message = $this->replaceBrandNames(base64_decode($data['message']));
        $recipients = $data['recipients'];

        Mail::to($recipients)->send(new SetEmailData($subject, $message));

        return "email sent successfully!";
        } catch (\Throwable $th) {
            //throw $th;
        }


    }
}

?>