<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use Illuminate\Http\Request;

class CategoriesController extends Controller
{
    public function index() {
        $product_categories = ProductCategory::all();

        return [
            'product_categories' => $product_categories
        ];
    }

    public function show($category_id) {
        $product_category = ProductCategory::findOrFail($category_id);

        return [
            'product_category' => $product_category
        ];
    }

    public function store(Request $request) {
        $request->validate([
            'name' => 'required|unique:product_categories,name',
        ]);

        $product_category = ProductCategory::create($request->all());

        return [
            'product_category' => $product_category
        ];
    }

    public function update(Request $request, $category_id) {
        $request->validate([
            'name' => 'required|unique:product_categories,name,' . $category_id,
        ]);

        $product_category = ProductCategory::findOrFail($category_id);

        $product_category->update($request->all());

        return [
            'product_category' => $product_category
        ];
    }

    public function destroy($category_id) {

        $product_category = ProductCategory::findOrFail($category_id);

        $product_category->delete();

        return [
            'message' => 'Category Deleted'
        ];
    }
}
