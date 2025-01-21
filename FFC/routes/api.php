<?php

use App\Http\Controllers\Common\SuggestionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Common\CommonController;
use App\Http\Controllers\Common\ReceiverDetailsController;
use App\Http\Controllers\Common\ServiceTypeController;
use App\Http\Controllers\Customer\CustomerController;
use App\Http\Controllers\Destination\DestinationController;
use App\Http\Controllers\Order\OrderController;
use App\Http\Controllers\Order\OrderStatusMasterController;
use App\Http\Controllers\Permission\PermissionController;
use App\Http\Controllers\Port\PortController;
use App\Http\Controllers\Quote\QuoteController;
use App\Http\Controllers\Rate\RateController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Vendor\VendorController;
use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\CheckTokenExpiry;

// Public Routes
// Route::post('login', [AuthController::class, 'login']);
// Route::post('refresh', [AuthController::class, 'refreshToken']);

Route::prefix('user')->group(
    function () {
        Route::controller(AuthController::class)->group(
            function () {
                Route::post('/login', action: 'login');
                Route::post('/checkEmailExists', 'checkEmailExists');
                Route::post('/checkMobileExists', 'checkMobileExists');
                Route::post('/refresh', 'refreshToken');
                Route::post('/sendOtp/{id}', 'sendOtp');
                Route::post('/verifyOtp', 'verifyOtp');
                Route::post('/reset-password', 'resetPassword');
                Route::post('/verify-reset-password', 'verifyResetPassword');
                Route::post('/update-reset-password', 'updateResetPassword');
            }
        );
        Route::controller(UserController::class)->group(
            function () {
                // Route::post('/register', 'store');
            }
        );
    }
);

// Group for routes that require authentication and token expiration check
Route::middleware(['auth:api', CheckTokenExpiry::class])
    ->group(function () {
        Route::get('/test', [AuthController::class, 'test']); // Protected route

        // Update Reset Password
        Route::prefix('user')->group(
            function () {
                Route::controller(AuthController::class)->group(function () {
                    // Route::post('/update-reset-password/{id}', 'updateResetPassword');
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
                    Route::post('/index', 'index')->middleware(CheckPermission::class . ':User,can_view');
                    Route::post('/register', 'store')->middleware(CheckPermission::class . ':User,can_create');
                    Route::post('/update/{id}', 'update')->middleware(CheckPermission::class . ':User,can_edit');
                    Route::get('/edit/{id}', 'edit')->middleware(CheckPermission::class . ':User,can_edit');
                    Route::post('/status/{id}', 'status')->middleware(CheckPermission::class . ':User,can_edit');
                    Route::delete('/delete/{id}', action: 'destroy')->middleware(CheckPermission::class . ':User,can_delete');
                    Route::post('/export', 'excelExport');
                });
            }
        );

        //Verndor Management
        Route::prefix('vendor')->group(
            function () {
                Route::controller(VendorController::class)->group(function () {
                    Route::post('/index', 'index')->middleware(CheckPermission::class . ':Vendor,can_view');
                    Route::post('/save', action: 'store')->middleware(CheckPermission::class . ':Vendor,can_create');
                    Route::get('/edit/{id}', 'edit')->middleware(CheckPermission::class . ':Vendor,can_edit');
                    Route::post('/update/{id}', action: 'update')->middleware(CheckPermission::class . ':Vendor,can_edit');
                    Route::delete('/delete/{id}', action: 'destroy')->middleware(CheckPermission::class . ':Vendor,can_delete');
                    Route::post('/status/{id}', 'status')->middleware(CheckPermission::class . ':Vendor,can_edit');
                    Route::post('/import', 'excelUpload');
                    Route::post('/export', 'excelExport');
                    Route::get('/vendorType', 'getAllVendorType');
                    Route::post('/saveVendorType', 'storeVendorType');
                    Route::post('/vendorTypeStatus/{id}', 'vendorStatus');
                });
            }
        );

        //Customer Management
        Route::prefix('customer')->group(
            function () {
                Route::controller(CustomerController::class)->group(function () {
                    Route::post('/index', 'index')->middleware(CheckPermission::class . ':Customer,can_view');
                    Route::post('/save', 'store')->middleware(CheckPermission::class . ':Customer,can_create');
                    Route::get('/edit/{id}', 'edit')->middleware(CheckPermission::class . ':Customer,can_edit');
                    Route::post('/update/{id}', 'update')->middleware(CheckPermission::class . ':Customer,can_edit');
                    Route::delete('/delete/{id}', action: 'destroy')->middleware(CheckPermission::class . ':Customer,can_delete');
                    Route::post('/status/{id}', 'status')->middleware(CheckPermission::class . ':Customer,can_edit');
                    Route::post('/import', 'excelUpload');
                    Route::post('/export', 'excelExport');
                });
            }
        );

        //Permission
        Route::prefix('permission')->group(
            function () {
                Route::controller(PermissionController::class)->group(function () {
                    Route::get('/view/{id}', 'view');
                    Route::post('/saveUserPermissions/{id}', 'saveUserPermissions');
                    Route::get('/master', 'getAllMaster');
                    Route::post('/saveMaster', 'storeMaster');
                });
            }
        );

        //Common
        Route::prefix('common')->group(
            function () {
                Route::controller(CommonController::class)->group(function () {
                    Route::get('/country', 'country');
                    Route::get('/state/{id}', 'state');
                    Route::get('/city/{id}',  'city');
                    Route::post('/vendorList', 'getAllVendorList');
                    Route::post('/portList', 'getAllPortList');
                    Route::post('/destinationList', 'getAllDestinationList');
                    Route::post('/customerList', 'getAllCustomerList');
                    Route::post('/documentUpload', 'documentUpload');
                });
            }
        );

        Route::prefix('common/suggestion')->group(
            function () {
                Route::controller(SuggestionController::class)->group(function () {
                    Route::get('/customer', 'getSuggestionCustomer');
                    Route::post('/customer', 'storeSuggestionCustomer');
                    Route::get('/address', 'getSuggestionAddress');
                    Route::post('/address', 'storeSuggestionAddress');
                });
            }
        );

        Route::prefix('common/receiver')->group(
            function () {
                Route::controller(ReceiverDetailsController::class)->group(function () {
                    Route::get('/name', 'getReceiverName');
                    Route::post('/saveReceiverName', 'storeReceiverName');
                    Route::get('/address', 'getReceiverAddress');
                    Route::post('/saveReceiverAddress', 'storeReceiverAddress');
                });
            }
        );

        //Port
        Route::prefix('port')->group(
            function () {
                Route::controller(PortController::class)->group(function () {
                    Route::get('/port-type/{id?}', 'portType');
                    Route::post('/index', 'index')->middleware(CheckPermission::class . ':Port,can_view');
                    Route::post('/save', 'store')->middleware(CheckPermission::class . ':Port,can_create');
                    Route::get('/edit/{id}', 'edit')->middleware(CheckPermission::class . ':Port,can_edit');
                    Route::post('/update/{id}', 'update')->middleware(CheckPermission::class . ':Port,can_edit');
                    Route::delete('/delete/{id}', action: 'destroy')->middleware(CheckPermission::class . ':Port,can_delete');
                    Route::post('/status/{id}', 'status')->middleware(CheckPermission::class . ':Port,can_edit');
                    Route::post('/import', 'excelUpload');
                    Route::post('/export', 'excelExport');
                });
            }
        );

        //Designation
        Route::prefix('destination')->group(
            function () {
                Route::controller(DestinationController::class)->group(function () {
                    Route::post('/index', 'index')->middleware(CheckPermission::class . ':Destination,can_view');
                    Route::post('/save', 'store')->middleware(CheckPermission::class . ':Destination,can_create');
                    Route::get('/edit/{id}', 'edit')->middleware(CheckPermission::class . ':Destination,can_edit');
                    Route::post('/update/{id}', 'update')->middleware(CheckPermission::class . ':Destination,can_edit');
                    Route::delete('/delete/{id}', action: 'destroy')->middleware(CheckPermission::class . ':Destination,can_delete');
                    Route::post('/status/{id}', 'status')->middleware(CheckPermission::class . ':Destination,can_edit');
                    Route::post('/import', 'excelUpload');
                    Route::post('/export', 'excelExport');
                });
            }
        );

        // Rate
        Route::prefix('rate')->group(
            function () {
                Route::controller(RateController::class)->group(function () {
                    Route::post('/index', 'index')->middleware(CheckPermission::class . ':Rate,can_view');
                    Route::post('/save', 'store')->middleware(CheckPermission::class . ':Rate,can_create');
                    Route::get('/edit/{id}', 'edit')->middleware(CheckPermission::class . ':Rate,can_edit');
                    Route::post('/update/{id}', 'update')->middleware(CheckPermission::class . ':Rate,can_edit');
                    Route::delete('/delete/{id}', action: 'destroy')->middleware(CheckPermission::class . ':Rate,can_delete');
                    Route::post('/status/{id}', 'status')->middleware(CheckPermission::class . ':Rate,can_edit');
                    Route::post('/rateNotes/{id}', 'getRateNote');
                    Route::post('/saveNote', 'storeNote')->middleware(CheckPermission::class . ':RateNote,can_create');
                    Route::post('/updateNote/{id}', 'updateNote')->middleware(CheckPermission::class . ':RateNote,can_edit');
                    Route::get('/editNote/{id}', 'editNote')->middleware(CheckPermission::class . ':RateNote,can_edit');
                    Route::delete('/deleteNote/{id}', action: 'destroyNote')->middleware(CheckPermission::class . ':RateNote,can_delete');
                    Route::post('/statusNote/{id}', 'statusNote')->middleware(CheckPermission::class . ':RateNote,can_edit');
                    Route::post('/import', 'excelUpload');
                    Route::post('/export', 'excelExport');
                });
            }
        );

        // Quotes
        Route::prefix('quote')->group(
            function (): void {
                Route::controller(QuoteController::class)->group(function () {
                    Route::post('/getVendorList', 'getVendorList');
                    Route::post('/index', 'index')->middleware(CheckPermission::class . ':Quote,can_view');
                    Route::post('/save', 'store')->middleware(CheckPermission::class . ':Quote,can_create');
                    Route::get('/edit/{id}', 'edit')->middleware(CheckPermission::class . ':Quote,can_edit');
                    Route::post('/update/{id}', 'update')->middleware(CheckPermission::class . ':Quote,can_edit');
                    Route::delete('/delete/{id}', action: 'destroy')->middleware(CheckPermission::class . ':Quote,can_delete');
                    Route::post('/status/{id}', 'status')->middleware(CheckPermission::class . ':Quote,can_edit');
                    Route::post('/quoteNotes/{id}', 'getQuoteNote')->middleware(CheckPermission::class . ':QuoteNote,can_view');
                    Route::post('/saveNote', 'storeNote')->middleware(CheckPermission::class . ':QuoteNote,can_create');
                    Route::get('/editNote/{id}', 'editNote')->middleware(CheckPermission::class . ':QuoteNote,can_edit');
                    Route::post('/updateNote/{id}', 'updateNote')->middleware(CheckPermission::class . ':QuoteNote,can_edit');
                    Route::delete('/deleteNote/{id}', action: 'destroyNote')->middleware(CheckPermission::class . ':QuoteNote,can_delete');
                    Route::post('/statusNote/{id}', 'statusNote')->middleware(CheckPermission::class . ':QuoteNote,can_edit');
                    Route::post('/export', 'excelExport');
                    Route::get('/pdf/{id}', 'generatePdf');
                });
            }
        );

        // Service Type
        Route::prefix('service')->group(
            function (): void {
                Route::controller(ServiceTypeController::class)->group(function () {
                    Route::get('/index', 'index');
                    Route::post('/save', 'store');
                    Route::get('/edit/{id}', 'edit');
                    Route::post('/update/{id}', 'update');
                    Route::post('/status/{id}', 'status');
                    Route::delete('/delete/{id}', action: 'destroy');
                });
            }
        );

        // Orders
        Route::prefix('order')->group(
            function (): void {
                Route::controller(OrderController::class)->group(function () {
                    Route::post('/index', 'index');
                    Route::post('/save', 'store');
                    Route::get('/edit/{id}', 'edit');
                    Route::post('/update/{id}', 'update');
                    Route::delete('/delete/{id}', action: 'destroy');
                    Route::post('/orderNotes/{id}', 'getOrderNote');
                    Route::post('/saveNote', 'storeNote');
                    Route::post('/updateNote/{id}', 'updateNote');
                    Route::get('/editNote/{id}', 'editNote');
                    Route::delete('/deleteNote/{id}', action: 'destroyNote');
                    Route::post('/statusNote/{id}', 'statusNote');
                    Route::post('/export', 'excelExport');
                    Route::get('/pdf/{id}', 'generatePdf');
                    Route::get('/orderStatusByServiceType/{id}', 'getOrderStatusByServiceType');
                    // Order Status
                    Route::post('/status/{orderId}', 'status');
                    Route::get('/deliveryStatus/{serviceTypeId}/{deliveryId}', 'deliveryStatus');
                    Route::post('/deliveryUpdateStatus/{deliveryId}', 'deliveryUpdateStatus');
                });
            }
        );

        Route::prefix('orderStatusMaster')->group(
            function (): void {
                Route::controller(OrderStatusMasterController::class)->group(function () {
                    Route::get('/index', 'index');
                    Route::post('/save', 'store');
                    Route::get('/edit/{id}', 'edit');
                    Route::post('/update/{id}', 'update');
                    Route::delete('/delete/{id}', action: 'destroy');
                    Route::post('/status/{id}', 'status');
                });
            }
        );
    });
