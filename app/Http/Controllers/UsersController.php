<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kutia\Larafirebase\Facades\Larafirebase;

class UsersController extends Controller
{
    public function updateFcmToken(Request $request) {
        $request->validate([
            'fcm_token' => 'required'
        ]);

        $request->user()->update(['fcm_token'=>$request->token]);

        return [
            'message' => 'FCM Token Updated'
        ];
    }

    public function updateLocation(Request $request) {
        $request->validate([
            'longitude' => 'required',
            'latitude' => 'required'
        ]);

        $request->user()->update([
            'longitude' => $request->longitude,
            'latitude' => $request->latitude,
        ]);

        return [
            'message' => 'Location Updated'
        ];
    }

    public function sendNotification() {
        Larafirebase::withTitle('LaraFirebase')
        ->withBody('Manok Man is now open')
        ->sendNotification([
            'fqMHr5KjSLWg7K9ka8m-eq:APA91bFHIOtnO49mQP8lxor8o-6CVw4TpekbQVwQE8oiyi2jkOlPEsuIspn2Eua6_ELUkVVCKQJmt0h0cqVSoUoQceaH-foYSJHhwBc2VHC5u-ZLHffm-RUfwJuHlBdX6vMnYe36lFkr'
        ]);
    }
}
