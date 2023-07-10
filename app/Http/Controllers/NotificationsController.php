<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationsController extends Controller
{
    public function index(Request $request) {
        $notifications = $request->user()->notifications;

        return [
            'notifications' => $notifications
        ];
    }
}
