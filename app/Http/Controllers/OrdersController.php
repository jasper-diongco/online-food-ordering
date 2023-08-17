<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Rating;
use App\Models\User;
use App\Notifications\NewOrderNotification;
use App\Notifications\OrderStatusUpdateNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Kutia\Larafirebase\Facades\Larafirebase;

class OrdersController extends Controller
{
    public function indexOfVendor(Request $request)
    {
        $store_id = $request->store_id ?? '';

        $orders = Order::where('store_id', $store_id)
            ->orderBy('created_at', 'DESC')
            ->with('order_details')
            ->with('user')
            ->with('store')
            ->get();

        foreach ($orders as $order) {
            $rating = Rating::where('order_id', $order->id)->first();

            $order->has_rating = $rating != null;
        }

        return [
            'orders' => $orders
        ];
    }

    public function indexOfCustomer(Request $request)
    {
        $user_id = $request->user_id ?? '';

        $orders = Order::where('user_id', $user_id)
            ->orderBy('created_at', 'DESC')
            ->with('order_details')
            ->with('user')
            ->with('store')
            // ->with('product')
            ->get();

        foreach ($orders as $order) {
            $rating = Rating::where('order_id', $order->id)->first();

            $order->has_rating = $rating != null;
        }

        return [
            'orders' => $orders
        ];
    }


    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'store_id' => 'required',
            'address' => 'required',
            'phone_number' => 'required',
            'longitude' => 'required',
            'latitude' => 'required',
            'type' => 'required',
            'status' => 'required',
            'payment_method' => 'required',
        ]);

        $note = $request->note ?? '';

        // DB::transaction();

        $order = Order::create([
            ...$request->all(),
            'note' => $note
        ]);

        $cart_items = CartItem::where('user_id', $request->user_id)->get();

        foreach ($cart_items as $cart_item) {
            OrderDetail::create([
                'order_id' => $order->id,
                'product_id' => $cart_item->product_id,
                'quantity' => $cart_item->quantity,
                'price' => $cart_item->product->price,
            ]);

            $cart_item->delete();
        }

        // DB::commit();

        $store_owner = User::find($order->store->user_id);

        Notification::sendNow([$store_owner], new NewOrderNotification($order));

        if ($store_owner->fcm_token) {
            Larafirebase::withTitle('You have a new order')
                ->withBody($order->user->name . ' placed a new order.')
                ->sendNotification([
                    $store_owner->fcm_token
                ]);
        }
        

        $order->load('order_details');

        return [
            'order' => $order
        ];
    }

    public function updateStatus(Request $request, $order_id)
    {
        $order = Order::findOrFail($order_id);

        $order->status = $request->status;
        $order->update();

        if ($order->user->fcm_token) {
            Larafirebase::withTitle($order->store->store_name)
                ->withBody('Your order is now ' . $order->status)
                ->sendNotification([
                    $order->user->fcm_token
                ]);
        }
        

        Notification::sendNow([$order->user], new OrderStatusUpdateNotification($order));

        return [
            'order' => $order
        ];
    }
}
