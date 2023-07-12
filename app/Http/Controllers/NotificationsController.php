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

    public function readNotifications(Request $request) {
        $request->user()->unreadNotifications->markAsRead();

        return [
            'message' => 'Notifications Read'
        ];
    }
}
