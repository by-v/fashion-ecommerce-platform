<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\TrackOrderController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/shop', [ShopController::class, 'index'])->name('shop');
Route::get('/product/{slug}', [ProductController::class, 'show'])->name('product.show');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/cart', [CartController::class, 'index'])->name('cart');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::patch('/cart/{item}', [CartController::class, 'updateQuantity'])->name('cart.update');
    Route::delete('/cart/{item}', [CartController::class, 'remove'])->name('cart.remove');

    Route::get('/checkout/cart', [CheckoutController::class, 'fromCart'])->name('checkout.cart');
    Route::post('/checkout/buy-now', [CheckoutController::class, 'buyNow'])->name('checkout.buyNow');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/order/{orderNumber}/confirmation', [OrderController::class, 'confirmation'])->name('order.confirmation');

    Route::get('/payment/{orderNumber}/snap-token', [PaymentController::class, 'getSnapToken'])->name('payment.snapToken');
});

Route::post('/payment/notification', [PaymentController::class, 'notification'])->name('payment.notification');

Route::get('/track-order', [TrackOrderController::class, 'index'])->name('track.order');
Route::post('/track-order', [TrackOrderController::class, 'track'])->name('track.order.search')->middleware('throttle:10,1');

Route::get('/admin-demo', function () {
    return view('admin-demo');
})->name('admin.demo');
require __DIR__.'/auth.php';
