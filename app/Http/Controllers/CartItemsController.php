<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use Illuminate\Http\Request;

class CartItemsController extends Controller
{
    public function index(Request $request) {
        $user_id = $request->user_id ?? '';

        $cart_items = CartItem::with('product')->get();
        // $cart_items = CartItem::all();
        return [
            'cart_items' => $cart_items
        ];
    }

    public function store(Request $request) {
        $request->validate([
            'user_id' => 'required',
            'store_id' => 'required',
            'product_id' => 'required',
            'quantity' => 'required',
        ]);
    
        $cart_item = CartItem::where('store_id', $request->store_id)
            ->where('product_id', $request->product_id)
            ->first();
    
        if ($cart_item) {
            // If the product already exists in the cart, update the quantity
            $cart_item->quantity += $request->quantity;
            $cart_item->save();
        } else {
            // If the product doesn't exist, create a new cart item
            $cart_item = CartItem::create($request->all());
        }
    
        // Remove all cart items for the user if the cart is empty
        $cart_count = CartItem::where('store_id', $request->store_id)->count();
        if ($cart_count == 0) {
            CartItem::where('user_id', $request->user_id)->delete();
        }
    
        return [
            'cart_item' => $cart_item
        ];
    }
    

    public function update(Request $request, $cart_item_id) {
        $request->validate([
            'quantity' => 'required'
        ]);

        $cart_item = CartItem::findOrFail($cart_item_id);

        $cart_item->update([
            'quantity' => $request->quantity
        ]);

        return [
            'cart_item' => $cart_item
        ];
    }

    public function destroy($cart_item_id) {

        $cart_item = CartItem::findOrFail($cart_item_id);

        $cart_item->delete();

        return [
            'message' => 'Cart Item Deleted'
        ];
    }
}
