<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Kutia\Larafirebase\Facades\Larafirebase;
use Intervention\Image\Facades\Image;

class UsersController extends Controller
{
    public function uploadImage(Request $request, $user_id)
    {
        $request->validate([
            'image' => 'image|max:10000'
        ]);

        $user = User::findOrFail($user_id);

        $image_name = uniqid() . '_' . pathinfo($request->image->getClientOriginalName(), PATHINFO_FILENAME) . '.png';
        $image = Image::make($request->image);
        $image->fit(1200, 1200);
        $image->save(public_path('storage/uploads/' . $image_name), 90, 'png');

        $user->update(['image' => $image_name]);

        return [
            'message' => 'Image Uploaded',
            'user' => $user
        ];
    }


    public function updateFcmToken(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required'
        ]);

        $request->user()->update(['fcm_token' => $request->token]);

        return [
            'message' => 'FCM Token Updated'
        ];
    }

    public function updateLocation(Request $request)
    {
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

    public function sendNotification()
    {
        Larafirebase::withTitle('LaraFirebase')
            ->withBody('Manok Man is now open')
            ->sendNotification([
                'fqMHr5KjSLWg7K9ka8m-eq:APA91bFHIOtnO49mQP8lxor8o-6CVw4TpekbQVwQE8oiyi2jkOlPEsuIspn2Eua6_ELUkVVCKQJmt0h0cqVSoUoQceaH-foYSJHhwBc2VHC5u-ZLHffm-RUfwJuHlBdX6vMnYe36lFkr'
            ]);
    }
}
