<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Store;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\NewProductNotification;
use App\Notifications\UpdateProductNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Intervention\Image\Facades\Image;
use Kutia\Larafirebase\Facades\Larafirebase;

class ProductsController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search ?? '';
        $user_id = $request->user_id ?? 0;

        $user = User::find($user_id);

        if ($user && $user->user_type == 'Vendor') {
            $store = Store::where('user_id', $user_id)->first();

            $products = Product::where('name', 'LIKE', '%' . $search . '%')
                ->where('store_id', $store->id)
                ->with('category')
                ->with('store')
                ->get();
        } else {
            $products = Product::where('name', 'LIKE', '%' . $search . '%')
                ->with('category')
                ->with('store')
                ->get();
        }



        return [
            'products' => $products
        ];
    }

    public function indexPerCategory(Request $request)
    {
        $category_id = $request->category_id ?? '';

        $products = Product::where('category_id', $category_id)
            ->with('category')
            ->with('store')
            ->get();

        return [
            'products' => $products
        ];
    }

    public function indexPerStore(Request $request)
    {
        $store_id = $request->store_id ?? '';

        $products = Product::where('store_id', $store_id)
            ->with('category')
            ->with('store')
            ->get();

        return [
            'products' => $products
        ];
    }

    public function groupByCategory(Request $request)
    {
        $store_id = $request->store_id ?? '';

        $categories = ProductCategory::select('product_categories.*')->distinct()->join('products', 'products.category_id', '=', 'product_categories.id')
            ->where('products.store_id', $store_id)
            ->get();

        $products = Product::where('store_id', $store_id)
            ->with('category')
            ->with('store')
            ->get();

        foreach ($categories as $category) {
            $list = collect([]);
            foreach ($products as $product) {
                if ($category->id == $product->category_id) {
                    $list->add($product);
                }
            }

            $category->products = $list;
        }

        return [
            'categories' => $categories
        ];
    }

    public function show($product_id)
    {
        $product = Product::where('id', $product_id)
            ->with('category')
            ->with('store')
            ->first();

        return [
            'product' => $product
        ];
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'description' => 'required',
            'price' => 'required',
            'category_id' => 'required',
            'store_id' => 'required'
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

        $product = Product::create([
            ...$request->all(),
            'image' => $image_name
        ]);

        //notification
        $user_subscribers = [];

        $subscribers = Subscription::where('store_id', $request->store_id)->get();

        foreach ($subscribers as $subscriber) {
            $user_subscribers[] = $subscriber->user;
        }


        Notification::sendNow($user_subscribers, new NewProductNotification($product));

        $this->notifySubscribers($product->store_id, 'We have a new product. It\'s ' . $product->name . ' and for only ' . $product->price . ' pesos.');

        return [
            'product' => $product
        ];
    }

    public function update(Request $request, $product_id)
    {
        $request->validate([
            'name' => 'required',
            'description' => 'required',
            'price' => 'required',
            'category_id' => 'required',
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

        $product = Product::findOrFail($product_id);

        if ($request->hasFile('image')) {
            $product->update([
                ...$request->all(),
                'image' => $image_name
            ]);
        } else {
            $product->update([
                ...$request->all()
            ]);
        }


        //notification
        $user_subscribers = [];

        $subscribers = Subscription::where('store_id', $product->store_id)->get();

        foreach ($subscribers as $subscriber) {
            $user_subscribers[] = $subscriber->user;
        }


        Notification::sendNow($user_subscribers, new UpdateProductNotification($product));

        $this->notifySubscribers($product->store_id, 'We have updated our product ' . $product->name);

        return [
            'product' => $product
        ];
    }

    public function notifySubscribers($store_id, $body)
    {
        $store = Store::find($store_id);

        $fcm_tokens = [];

        foreach ($store->subscribers as $subscriber) {
            if (!$subscriber->fcm_token) {
                continue;
            }

            $fcm_tokens[] = $subscriber->fcm_token;
        }

        Larafirebase::withTitle('Lalaco')
            ->withBody($store->store_name . ' - ' . $body)
            ->sendNotification($fcm_tokens);
    }

    public function destroy($product_id)
    {

        $product = Product::findOrFail($product_id);

        $product->delete();

        return [
            'message' => 'Product Deleted'
        ];
    }

    public function deleteProductsPerStore(Request $request)
    {
        $store_id = $request->store_id ?? '';

        // Delete products with the given store_id
        Product::where('store_id', $store_id)->delete();

        return [
            'message' => 'Products deleted successfully'
        ];
    }
}
