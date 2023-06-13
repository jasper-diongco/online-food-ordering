<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\Request;

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
