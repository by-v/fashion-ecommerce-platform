<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class TrackOrderController extends Controller
{
    public function index()
    {
        return view('track-order');
    }

    public function track(Request $request)
    {
        $request->validate([
            'order_number' => 'required|string',
        ]);

        $order = Order::with('items', 'shippingMethod')
            ->where('order_number', strtoupper($request->order_number))
            ->first();

        return view('track-order', compact('order'));
    }
}
