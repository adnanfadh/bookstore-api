<?php

use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\OrderProcessController;
use App\Http\Controllers\Api\ShoppingCartController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

route::post('/register', [UserController::class, 'register']);
route::post('/login', [UserController::class, 'login']);
route::post('/logout', [UserController::class, 'logout']);

Route::middleware('auth:api')->group(function () {
    Route::get('/profile', [UserController::class, 'profile']);
    Route::prefix('/users')->group(function(){
        Route::get('/list', [UserController::class, 'list']);
        Route::put('/update/{user}', [UserController::class, 'update']);
    });
    Route::apiResource('/books', BookController::class);
    Route::apiResource('/inventory', InventoryController::class);
    // Route::apiResource('/cart', ShoppingCartController::class);
    Route::prefix('/cart')->group(function(){
        Route::get('/', [ShoppingCartController::class, 'index']);
        Route::post('/', [ShoppingCartController::class, 'store']);
        Route::put('/{cart}', [ShoppingCartController::class, 'update']);
        Route::delete('/{cart}', [ShoppingCartController::class, 'destroy']);
        Route::post('/order', [OrderProcessController::class, 'store']);
        Route::get('/order/{order}', [OrderProcessController::class, 'show']);
    });

    Route::put('/order/payment/{order}', [OrderProcessController::class, 'payment']);
    Route::put('/order/payment-verify/{order}', [OrderProcessController::class, 'paymentVerify']);
    Route::put('/order/completed/{order}', [OrderProcessController::class, 'completeVerify']);
});


