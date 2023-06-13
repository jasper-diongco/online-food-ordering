<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use Illuminate\Http\Request;

class RatingsController extends Controller
{
    public function index(Request $request) {
        $store_id = $request->store_id ?? '';

        $ratings = Rating::where('store_id', $store_id)
            ->orderBy('created_at', 'DESC')
            ->get();

        return [
            'ratings' => $ratings
        ];
    }

    public function show($rating_id) {
        $rating = Rating::findOrFail($rating_id);

        return [
            'rating' => $rating
        ];
    }

    public function store(Request $request) {
        $request->validate([
            'store_id' => 'required',
            'user_id' => 'required',
            'comment' => 'required',
            'rate' => 'required',
        ]);

        $rating = Rating::create($request->all());

        return [
            'rating' => $rating
        ];
    }

    public function update(Request $request, $rating_id) {
        $request->validate([
            'store_id' => 'required',
            'user_id' => 'required',
            'comment' => 'required',
            'rate' => 'required',
        ]);

        $rating = Rating::findOrFail($rating_id);

        $rating->update($request->all());

        return [
            'rating' => $rating
        ];
    }
}
