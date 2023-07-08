<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Http\Request;

class OrderDetailController extends Controller
{
    public function indexOfOrder(Request $request) {
        $order_id = $request->order_id ?? '';

        $order_details = OrderDetail::where('order_id', $order_id)
            ->orderBy('created_at', 'DESC')
            ->with('product')
            ->get();

        return [
            'order_details' => $order_details
        ];
    }
}
