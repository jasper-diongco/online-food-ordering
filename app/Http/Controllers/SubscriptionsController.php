<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\User;
use App\Notifications\NewSubscriberNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class SubscriptionsController extends Controller
{
    
    public function indexOfVendor(Request $request) {
        $store_id = $request->store_id ?? '';

        $subscriptions = Subscription::where('store_id', $store_id)
            ->with('user')
            ->with('store')
            ->get();

        return [
            'subscriptions' => $subscriptions
        ];
    }

    public function countOfVendor(Request $request) {
        $store_id = $request->store_id ?? '';

        $subscription_count = Subscription::where('store_id', $store_id)
            ->count();

        return [
            'subscription_count' => $subscription_count
        ];
    }

    public function indexOfCustomer(Request $request) {
        $user_id = $request->user_id ?? '';

        $subscriptions = Subscription::where('user_id', $user_id)
            ->with('user')
            ->with('store')
            ->get();

        return [
            'subscriptions' => $subscriptions
        ];
    }

    public function store(Request $request) {
        $request->validate([
            'user_id' => 'required',
            'store_id' => 'required'
        ]);

        $subscription = Subscription::where('store_id', $request->store_id)
                            ->where('user_id', $request->user_id)
                            ->first();
        
        if ($subscription) {
            abort(422, 'Already Subscribed');
        }


        $subscription = Subscription::create($request->all());

        $store_owner = User::where('id', $subscription->store->user_id)->first();

        Notification::sendNow([$store_owner], new NewSubscriberNotification($subscription));

        return [
            'subscription' => $subscription
        ];
    }

    public function destroy(Request $request) {

        $subscription = Subscription::where('user_id', $request->user_id)
                            ->where('user_id', $request->user_id)
                            ->first();

        $subscription->delete();

        return [
            'message' => 'Subscription Deleted'
        ];
    }
}
