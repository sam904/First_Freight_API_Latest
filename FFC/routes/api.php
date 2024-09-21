<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\User\UserController;
use App\Http\Middleware\CheckTokenExpiry;

// Public Routes
Route::post('login', [AuthController::class, 'login']);
Route::post('refresh', [AuthController::class, 'refreshToken']);
Route::post('register', [UserController::class, 'store']);



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
    });
