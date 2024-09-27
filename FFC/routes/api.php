<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Customer\CustomerController;
use App\Http\Controllers\Permission\PermissionController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Vendor\VendorController;
use App\Http\Middleware\CheckTokenExpiry;

// Public Routes
// Route::post('login', [AuthController::class, 'login']);
// Route::post('refresh', [AuthController::class, 'refreshToken']);

Route::prefix('user')->group(
    function () {
        Route::controller(AuthController::class)->group(
            function () {
                Route::post('/login', action: 'login');
                Route::post('/refresh', 'refreshToken');
                Route::post('/sendOtp/{id}', 'sendOtp');
                Route::post('/verifyOtp', 'verifyOtp');
                Route::post('/reset-password', 'resetPassword');
                Route::post('/verify-reset-password', 'verifyResetPassword');
            }
        );
        Route::controller(UserController::class)->group(
            function () {
                Route::post('/register', 'store');
            }
        );
    }
);

// Group for routes that require authentication and token expiration check
Route::middleware(['auth:api', CheckTokenExpiry::class])
    ->group(function () {
        Route::get('/test', [AuthController::class, 'test']); // Protected route
        Route::prefix('user')->group(
            function () {
                Route::controller(AuthController::class)->group(function () {
                    Route::post('/update-reset-password', 'updateResetPassword');
                });
            }
        );

        Route::get('/user', function (Request $request) {
            return $request->user();
        });

        //UserManagment
        Route::prefix('user')->group(
            function () {
                Route::controller(UserController::class)->group(function () {
                    Route::get('/index', 'index');
                    Route::post('/update/{id}', 'update');
                    Route::get('/edit/{id}', 'edit');
                    Route::post('/status/{id}', 'status');
                    Route::delete('/delete/{id}', action: 'destroy');
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
                    Route::post('/status/{id}', 'status');
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
                    Route::post('/status/{id}', 'status');
                });
            }
        );

        //Permission
        Route::prefix('permission')->group(
            function () {
                Route::controller(PermissionController::class)->group(function () {
                    Route::get('/view/{id}', 'view');
                    Route::get('/master', 'index');
                    Route::post('/saveUserPermissions/{id}', 'saveUserPermissions');
                });
            }
        );
    });
