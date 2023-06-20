<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;

class ProductsController extends Controller
{
    public function index(Request $request) {
        $search = $request->search ?? '';

        $products = Product::where('name', 'LIKE', '%' . $search . '%')
            ->with('category')
            ->with('store')
            ->get();

        return [
            'products' => $products
        ];
    }

    public function indexPerCategory(Request $request) {
        $category_id = $request->category_id ?? '';

        $products = Product::where('category_id', $category_id)
            ->with('category')
            ->with('store')
            ->get();

        return [
            'products' => $products
        ];
    }

    public function indexPerStore(Request $request) {
        $store_id = $request->store_id ?? '';

        $products = Product::where('store_id', $store_id)
            ->with('category')
            ->with('store')
            ->get();

        return [
            'products' => $products
        ];
    }

    public function groupByCategory(Request $request) {
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

    public function show($product_id) {
        $product = Product::where('id', $product_id)
            ->with('category')
            ->with('store')
            ->first();

        return [
            'product' => $product
        ];
    }

    public function store(Request $request) {
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
            $image->save(public_path('storage/uploads/'. $image_name .'.png'), 90, 'png');
            
            $image_name = $image_name .'.png';
        }

        $product = Product::create([
            ...$request->all(),
            'image' => $image_name
        ]);

        return [
            'product' => $product
        ];
    }

    public function update(Request $request, $product_id) {
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
            $image->save(public_path('storage/uploads/'. $image_name .'.png'), 90, 'png');
            
            $image_name = $image_name .'.png';
        }

        $product = Product::findOrFail($product_id);

        $product->update([
            ...$request->all(),
            'image' => $image_name
        ]);

        return [
            'product' => $product
        ];
    }

    public function destroy($product_id) {

        $product = Product::findOrFail($product_id);

        $product->delete();

        return [
            'message' => 'Product Deleted'
        ];
    }
}
