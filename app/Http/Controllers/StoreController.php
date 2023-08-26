<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\Subscription;
use App\Models\User;
use App\Utils\CoordinateHelper;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;
use Kutia\Larafirebase\Facades\Larafirebase;

class StoreController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search ?? '';
        $user_id = $request->user_id ?? '';

        $user = User::find($user_id);


        if ($search !== '') {
            if ($user && $user->user_type == 'Vendor') {
                $stores = Store::where('store_name', 'LIKE', '%' . $search . '%')
                    ->where('is_active', 1)
                    ->where('user_id', $user->id)
                    ->with('schedules')
                    ->get();
            } else {
                $stores = Store::where('store_name', 'LIKE', '%' . $search . '%')->where('is_active', 1)->with('schedules')->get();
            }
        } else {
            if ($user && $user->user_type == 'Vendor') {
                $stores = Store::where('is_active', 1)
                    ->where('user_id', $user->id)
                    ->with('schedules')
                    ->get();
            } else {
                $stores = Store::where('is_active', 1)->with('schedules')->get();
            }
        }

        foreach ($stores as $store) {
            $subscription_count = Subscription::where('store_id', $store->id)
                ->count();

            $is_user_subscribed = Subscription::where('store_id', $store->id)
                ->where('user_id', $user_id)
                ->count();

            $store->is_user_subscribed = $is_user_subscribed;

            $store->subscription_count = $subscription_count;
        }

        return [
            'stores' => $stores
        ];
    }

    public function show(Request $request, $store_id)
    {
        $user_id = $request->user_id ?? '';

        $store = Store::where('id', $store_id)
            ->with('schedules')
            ->first();

        $subscription_count = Subscription::where('store_id', $store_id)
            ->count();

        $is_user_subscribed = Subscription::where('store_id', $store_id)
            ->where('user_id', $user_id)
            ->count();

        $store->subscription_count = $subscription_count;
        $store->is_user_subscribed = $is_user_subscribed;

        return [
            'store' => $store
        ];
    }


    public function showByUserId($user_id)
    {
        $store = Store::where('user_id', $user_id)->with('schedules')->first();

        $subscription_count = Subscription::where('store_id', $store->id)
            ->count();

        $store->subscription_count = $subscription_count;
        $store->is_user_subscribed = 0;

        return [
            'store' => $store
        ];
    }

    public function store(Request $request)
    {
        $request->validate([
            'store_name' => 'required',
            'store_description' => 'required',
            'location_description' => 'required',
            'longitude' => 'required',
            'latitude' => 'required'
        ]);

        $image_name = '';

        if ($request->hasFile('image')) {
            $request->validate([
                'image' => 'image|max:10000'
            ]);

            $image_name = uniqid() . '_' . pathinfo($request->image->getClientOriginalName(), PATHINFO_FILENAME);
            $image = Image::make($request->image);
            $image->fit(1200, 1200);
            $image->save(public_path('storage/uploads/' . $image_name . '.png'), 90, 'png');

            $image_name = $image_name . '.png';
        }

        $store = Store::create([
            ...$request->all(),
            'image' => $image_name
        ]);

        return [
            'store' => $store
        ];
    }

    public function update(Request $request, $store_id)
    {
        $request->validate([
            'store_name' => 'required',
            'store_description' => 'required',
            'location_description' => 'required',
            'longitude' => 'required',
            'latitude' => 'required'
        ]);

        $image_name = '';

        if ($request->hasFile('image')) {
            $request->validate([
                'image' => 'image|max:10000'
            ]);

            $image_name = uniqid() . '_' . pathinfo($request->image->getClientOriginalName(), PATHINFO_FILENAME);
            $image = Image::make($request->image);
            $image->fit(1200, 1200);
            $image->save(public_path('storage/uploads/' . $image_name . '.png'), 90, 'png');

            $image_name = $image_name . '.png';
        }

        $store = Store::findOrFail($store_id);

        if ($request->hasFile('image')) {
            $store->update([
                ...$request->all(),
                'image' => $image_name,
                'is_active' => 1
            ]);
        } else {
            $store->update([
                ...$request->all(),
                'is_active' => 1
            ]);
        }




        $this->notifySubscribers($store);

        return [
            'store' => $store
        ];
    }

    public function notifySubscribers($store)
    {
        $fcm_tokens = [];
        $subscribers = [];

        foreach ($store->subscribers as $subscriber) {
            if (!$subscriber->latitude || !$subscriber->fcm_token) {
                continue;
            }

            $distance = CoordinateHelper::computeDistance($store->latitude, $store->longitude, $subscriber->latitude, $subscriber->longitude, "K");

            if ($distance <= 2) {
                $fcm_tokens[] = $subscriber->fcm_token;
                $subscribers[] = $subscriber;
            }
        }

        Larafirebase::withTitle('Lalaco')
            ->withBody($store->store_name . ' is nearby. Check the menu and order now!')
            ->sendNotification($fcm_tokens);
    }

    public function updateLocation(Request $request, $store_id)
    {
        $request->validate([
            'longitude' => 'required',
            'latitude' => 'required'
        ]);

        $store = Store::findOrFail($store_id);

        $store->update([
            'longitude' => $request->longitude,
            'latitude' => $request->latitude,
        ]);

        $fcm_tokens = [];
        $subscribers = [];

        foreach ($store->subscribers as $subscriber) {
            if (!$subscriber->latitude || !$subscriber->fcm_token) {
                continue;
            }
            $distance = CoordinateHelper::computeDistance($store->latitude, $store->longitude, $subscriber->latitude, $subscriber->longitude, "K");

            if ($distance < 2) {
                $fcm_tokens[] = $subscriber->fcm_token;
                $subscribers[] = $subscriber;
            }
        }

        Larafirebase::withTitle('Lalaco')
            ->withBody($store->store_name . ' is nearby. Check the their menu!')
            ->sendNotification($fcm_tokens);



        return [
            'store' => $store,
            'subscribers' => $subscribers
        ];
    }

    public function destroy($store_id)
    {

        $store = Store::findOrFail($store_id);

        $store->delete();

        return [
            'message' => 'Store Deleted'
        ];
    }
}
