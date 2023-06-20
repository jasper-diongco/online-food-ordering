<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Utils\CoordinateHelper;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;
use Kutia\Larafirebase\Facades\Larafirebase;

class StoreController extends Controller
{
    public function index(Request $request) {
        $search = $request->search ?? '';
        
        if ($search !== '') {
            $stores = Store::where('store_name', 'LIKE', '%' . $search . '%')->get();
        } else {
            $stores = Store::all();
        }

        

        return [
            'stores' => $stores
        ];
    }

    public function show($store_id) {
        $store = Store::findOrFail($store_id);

        return [
            'store' => $store
        ];
    }

    public function store(Request $request) {
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
            $image->save(public_path('storage/uploads/'. $image_name .'.png'), 90, 'png');
            
            $image_name = $image_name .'.png';
        }

        $store = Store::create([
            ...$request->all(),
            'image' => $image_name
        ]);

        return [
            'store' => $store
        ];
    }

    public function update(Request $request, $store_id) {
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
            $image->save(public_path('storage/uploads/'. $image_name .'.png'), 90, 'png');
            
            $image_name = $image_name .'.png';
        }

        $store = Store::findOrFail($store_id);

        $store->update([
            ...$request->all(),
            'image' => $image_name
        ]);

        return [
            'store' => $store
        ];
    }

    public function updateLocation(Request $request, $store_id) {
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
            if (!$subscriber->latitude) { continue; }
            $distance = CoordinateHelper::computeDistance($store->latitude, $store->longitude, $subscriber->latitude, $subscriber->longitude, "K");

            if ($distance < 2) {
                $fcm_tokens[] = $subscriber->fcm_token;
                $subscribers[] = $subscriber;
            }
        }

        Larafirebase::withTitle('Lalaco')
        ->withBody($store->store_name . ' is nearby. Check the menu and enjoy eating! 👌')
        ->sendNotification($fcm_tokens);



        return [
            'store' => $store,
            'subscribers' => $subscribers
        ];
    }
}
