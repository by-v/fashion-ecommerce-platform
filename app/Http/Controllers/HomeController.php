<?php

namespace App\Http\Controllers;

use App\Models\Product;

class HomeController extends Controller
{
    public function index()
    {
        $heroProduct = Product::where('is_featured', true)->where('is_active', true)->first()
            ?? Product::where('is_active', true)->first();

        return view('home', compact('heroProduct'));
    }
}
