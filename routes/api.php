<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BannersController;
use App\Http\Controllers\CartItemsController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\NotificationsController;
use App\Http\Controllers\OrderDetailController;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\RatingsController;
use App\Http\Controllers\SchedulesController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\SubscriptionsController;
use App\Http\Controllers\UsersController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
// Route::post('/forgot_password', [ForgotPasswordController::class, 'store']);
// Route::patch('/forgot_password/update', [ForgotPasswordController::class, 'updatePassword']);
Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware(['auth:sanctum']);
Route::post('/update/{id}', [AuthController::class, 'update'])->middleware(['auth:sanctum']);
Route::post('/update/{id}/update_password', [AuthController::class, 'updatePassword'])->middleware(['auth:sanctum']);

Route::post('/users/{user_id}/upload_image', [UsersController::class, 'uploadImage']);
Route::put('/users/update_location', [UsersController::class, 'updateLocation'])->middleware(['auth:sanctum']);
Route::put('/users/update_fcm_token', [UsersController::class, 'updateFcmToken'])->middleware(['auth:sanctum']);
Route::get('/users/send_notification', [UsersController::class, 'sendNotification']);

Route::post('/stores', [StoreController::class, 'store']);
Route::put('/stores/{store_id}', [StoreController::class, 'update']);
Route::put('/stores/{store_id}/update_location', [StoreController::class, 'updateLocation']);
Route::get('/stores/by_user_id/{user_id}', [StoreController::class, 'showByUserId']);
Route::get('/stores/{store_id}', [StoreController::class, 'show']);
Route::get('/stores', [StoreController::class, 'index']);

Route::post('/categories', [CategoriesController::class, 'store']);
Route::put('/categories/{category_id}', [CategoriesController::class, 'update']);
Route::get('/categories/{category_id}', [CategoriesController::class, 'show']);
Route::get('/categories', [CategoriesController::class, 'index']);
Route::delete('/categories/{category_id}', [CategoriesController::class, 'destroy']);

Route::post('/products', [ProductsController::class, 'store']);
Route::put('/products/{product_id}', [ProductsController::class, 'update']);
Route::get('/products/search', [ProductsController::class, 'index']);
Route::get('/products/per_category', [ProductsController::class, 'indexPerCategory']);
Route::get('/products/per_store', [ProductsController::class, 'indexPerStore']);
Route::get('/products/group_by_category', [ProductsController::class, 'groupByCategory']);
Route::get('/products/{product_id}', [ProductsController::class, 'show']);
Route::delete('/products/{product_id}', [ProductsController::class, 'destroy']);

Route::get('/subscriptions/customer', [SubscriptionsController::class, 'indexOfCustomer']);
Route::get('/subscriptions/vendor', [SubscriptionsController::class, 'indexOfVendor']);
Route::post('/subscriptions', [SubscriptionsController::class, 'store']);
Route::delete('/subscriptions', [SubscriptionsController::class, 'destroy']);

Route::post('/schedules', [SchedulesController::class, 'store']);
Route::put('/schedules/{schedule_id}', [SchedulesController::class, 'update']);
Route::get('/schedules/{schedule_id}', [SchedulesController::class, 'show']);
Route::get('/schedules', [SchedulesController::class, 'index']);
Route::delete('/schedules/{schedule_id}', [SchedulesController::class, 'destroy']);

Route::post('/ratings', [RatingsController::class, 'store']);
Route::put('/ratings/{rating_id}', [RatingsController::class, 'update']);
Route::get('/ratings/{rating_id}', [RatingsController::class, 'show']);
Route::get('/ratings', [RatingsController::class, 'index']);

Route::post('/cart_items', [CartItemsController::class, 'store']);
Route::put('/cart_items/{cart_item_id}', [CartItemsController::class, 'update']);
Route::get('/cart_items', [CartItemsController::class, 'index']);
Route::delete('/cart_items/{cart_item_id}', [CartItemsController::class, 'destroy']);
Route::delete('/cart_items/delete_all/{store_id}', [CartItemsController::class, 'deleteAll']);

Route::post('/orders', [OrdersController::class, 'store']);
Route::get('/orders/vendor', [OrdersController::class, 'indexOfVendor']);
Route::get('/orders/customer', [OrdersController::class, 'indexOfCustomer']);
Route::put('/orders/{order_id}', [OrdersController::class, 'updateStatus']);

Route::get('/order_details/order', [OrderDetailController::class, 'indexOfOrder']);

Route::get('/banners', [BannersController::class, 'index']);

Route::get('/notifications', [NotificationsController::class, 'index'])->middleware(['auth:sanctum']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
