<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Customer\CustomerController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Vendor\VendorController;
use App\Http\Middleware\CheckTokenExpiry;

// Public Routes
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [UserController::class, 'store']);
Route::post('refresh', [AuthController::class, 'refreshToken']);


// Group for routes that require authentication and token expiration check
Route::middleware(['auth:api', CheckTokenExpiry::class])
    ->group(function () {
        Route::get('/test', [AuthController::class, 'test']); // Protected route

        Route::get('/user', function (Request $request) {
            return $request->user();
        });
        //UserManagment
        Route::prefix('user')->group(
            function () {
                Route::controller(UserController::class)->group(function () {
                    Route::get('/index', 'index');
                    Route::post('/update/{id}', 'update');
                });
            }
        );

        //Verndor Management
        Route::prefix('vendor')->group(
            function () {
                Route::controller(VendorController::class)->group(function () {
                    Route::get('/index', 'index');
                    Route::post('/save', action: 'store');
                    Route::get('/edit/{id}', 'edit');
                    Route::post('/update/{id}', action: 'update');
                    Route::delete('/delete/{id}', action: 'destroy');
                });
            }
        );

        //Customer Management
        Route::prefix('customer')->group(
            function () {
                Route::controller(CustomerController::class)->group(function () {
                    Route::get('/index', 'index');
                    Route::post('/save', 'store');
                    Route::get('/edit/{id}', 'edit');
                    Route::post('/update/{id}', 'update');
                    Route::delete('/delete/{id}', action: 'destroy');
                });
            }
        );
    });
