<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Http\Request;

class BannersController extends Controller
{
       public function index(Request $request) {
        $banners = Banner::all();
        return [
            'banners' => $banners
        ];
    }
}
