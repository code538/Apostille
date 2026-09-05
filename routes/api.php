<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

// Customer
use App\Http\Controllers\Api\Customer\CustomerController;
// Lawyer / Apostille Officer
use App\Http\Controllers\Api\Lawyer\ApostilleOfficerController;
// Business Client
use App\Http\Controllers\Api\Business\BusinessController;
// Administrator
use App\Http\Controllers\Api\Admin\AdminController;
// Super Admin
use App\Http\Controllers\Api\SuperAdmin\SuperAdminController;
// Finance
use App\Http\Controllers\Api\Finance\FinanceController;
// Courier
use App\Http\Controllers\Api\Courier\CourierController;
// Customer Support
use App\Http\Controllers\Api\Support\SupportController;

//Admin Controllers
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Admin\CountryController;
use App\Http\Controllers\Api\Admin\RegionController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\Admin\LawyerController;
use App\Http\Controllers\Api\Admin\LawyerDocumentController as AdminLawyerDocumentController;
use App\Http\Controllers\Api\Admin\ServiceDocumentRequirementController;
use App\Http\Controllers\Api\Admin\LawyerServicePricingController as AdminLawyerServicePricingController;
use App\Http\Controllers\Api\Admin\DeliveryMethodController;
use App\Http\Controllers\Api\Admin\DeliveryMethodRateController;

//Lawyer Controllers
use App\Http\Controllers\Api\Lawyer\LawyerProfileController;
use App\Http\Controllers\Api\Lawyer\LawyerDocumentController;
use App\Http\Controllers\Api\Lawyer\LawyerServiceRegionController;
use App\Http\Controllers\Api\Lawyer\LawyerServicePricingController;

// Customer Controllers
use App\Http\Controllers\Api\Customer\ServiceController as CustomerServiceController;
use App\Http\Controllers\Api\Customer\LocationController;
use App\Http\Controllers\Api\Customer\LawyerController as CustomerLawyerController;
use App\Http\Controllers\Api\Customer\ServiceDocumentRequirementController as CustomerServiceDocumentRequirementController;
use App\Http\Controllers\Api\Customer\OrderController;









Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->prefix('auth')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);

    Route::get('/services', [ServiceController::class,'index']);
    Route::get('/services/{service}', [ServiceController::class,'show']);
    Route::post('/services', [ServiceController::class,'store']);
    Route::put('/services/{service}', [ServiceController::class,'update']);
    Route::delete('/services/{service}', [ServiceController::class,'destroy']);
    Route::patch('/services/{service}/activate', [ServiceController::class,'activate']);
    Route::patch('/services/{service}/deactivate', [ServiceController::class,'deactivate']);
});

Route::middleware(['auth:sanctum','role:administrator,super-admin',])->prefix('admin')->group(function () {
    Route::get('/dashboard',[AdminController::class, 'dashboard']);
    Route::get( '/staff', [UserController::class, 'index'] );
    Route::get( '/staff/{user}', [UserController::class, 'show'] );
    Route::post( '/staff', [UserController::class, 'storeStaff'] );
    Route::put( '/staff/{user}', [UserController::class, 'update'] );
    Route::patch( '/staff/{user}/status', [UserController::class, 'updateStatus'] );
    Route::delete( '/staff/{user}', [UserController::class, 'destroy'] );

    Route::prefix('countries')->group(function () {
        Route::get( '/', [CountryController::class, 'index'] ); 
        Route::post( '/', [CountryController::class, 'store'] ); 
        Route::get( '/{country}', [CountryController::class, 'show'] ); 
        Route::put( '/{country}', [CountryController::class, 'update'] ); 
        Route::patch( '/{country}/status', [CountryController::class, 'updateStatus'] ); 
        Route::delete( '/{country}', [CountryController::class, 'destroy'] ); 
    }); 
    
    Route::prefix('regions')->group(function () { 
        Route::get( '/', [RegionController::class, 'index'] ); 
        Route::post( '/', [RegionController::class, 'store'] ); 
        Route::get( '/{region}', [RegionController::class, 'show'] ); 
        Route::put( '/{region}', [RegionController::class, 'update'] ); 
        Route::patch( '/{region}/status', [RegionController::class, 'updateStatus'] ); 
        Route::delete( '/{region}', [RegionController::class, 'destroy'] ); 
    });

    Route::prefix('services')->group(function () {
        Route::get('/', [ServiceController::class,'index']);
        Route::get('/{service}', [ServiceController::class,'show']);
        Route::post('', [ServiceController::class,'store']);
        Route::put('/{service}', [ServiceController::class,'update']);
        Route::delete('/{service}', [ServiceController::class,'destroy']);
        Route::patch('/{service}/status-change', [ServiceController::class,'statusChange']);
    });

    Route::prefix('lawyers')->group(function () {
        Route::get('/', [ LawyerController::class, 'index' ]); 
        Route::get('/{lawyer}', [ LawyerController::class, 'show' ]); 
        Route::post('/{lawyer}/review', [ LawyerController::class, 'review' ]); 
        Route::post('/{lawyer}/approve', [ LawyerController::class, 'approve' ]); 
        Route::post('/{lawyer}/reject', [ LawyerController::class, 'reject' ]); 
        Route::post('/{lawyer}/toggle-availability', [ LawyerController::class, 'toggleAvailability' ]);

        Route::get('/{lawyer}/documents', [ AdminLawyerDocumentController::class, 'index' ]); 
        Route::get('/{lawyer}/documents/{document}/view', [ AdminLawyerDocumentController::class, 'view' ]); 
        Route::post('/{lawyer}/documents/{document}/verify', [ AdminLawyerDocumentController::class, 'verify' ]); 
        Route::post('/{lawyer}/documents/{document}/reject', [ AdminLawyerDocumentController::class, 'reject' ]);
    });

    Route::prefix('service-document-requirements')->group(function () {
        Route::get('/', [ServiceDocumentRequirementController::class, 'index']);
        Route::post('/', [ServiceDocumentRequirementController::class, 'store']);
        Route::get('/{requirement}', [ServiceDocumentRequirementController::class, 'show']);
        Route::put('/{requirement}', [ServiceDocumentRequirementController::class, 'update']);
        Route::delete('/{requirement}', [ServiceDocumentRequirementController::class, 'destroy']);
        Route::patch('/{requirement}/status', [ServiceDocumentRequirementController::class, 'statusChange']);
    });
    //Route::get('/services/{service}/document-requirements', [ServiceDocumentRequirementController::class, 'serviceRequirements']);

    Route::prefix('lawyer-service-pricings')->group(function () {
        Route::get('/', [AdminLawyerServicePricingController::class, 'index']);
        Route::post('/', [AdminLawyerServicePricingController::class,'store']);
        Route::get('/{pricing}', [AdminLawyerServicePricingController::class, 'show']);
        Route::put('/{pricing}', [AdminLawyerServicePricingController::class, 'update']);
        Route::delete('/{pricing}', [AdminLawyerServicePricingController::class, 'destroy']);
        Route::patch('/{pricing}/status', [AdminLawyerServicePricingController::class, 'statusChange']);
    });

    Route::prefix('delivery-methods')->group(function () {
        Route::get('/', [DeliveryMethodController::class,'index']);
        Route::post('/', [DeliveryMethodController::class, 'store']);
        Route::get('/{deliveryMethod}', [DeliveryMethodController::class, 'show']);
        Route::put('/{deliveryMethod}', [DeliveryMethodController::class, 'update']);
        Route::delete('/{deliveryMethod}', [DeliveryMethodController::class, 'destroy']);
        Route::patch('/{deliveryMethod}/status', [DeliveryMethodController::class, 'statusChange']);
    });

    Route::prefix('delivery-method-rates')->group(function () {
        Route::get('/', [DeliveryMethodRateController::class, 'index']);
        Route::post('/', [DeliveryMethodRateController::class, 'store']);
        Route::get('/{deliveryMethodRate}', [DeliveryMethodRateController::class, 'show']);
        Route::put('/{deliveryMethodRate}', [DeliveryMethodRateController::class, 'update']);
        Route::delete('/{deliveryMethodRate}', [DeliveryMethodRateController::class, 'destroy']);
        Route::patch('/{deliveryMethodRate}/status', [DeliveryMethodRateController::class, 'statusChange']);
    });

});

Route::middleware(['auth:sanctum','role:super-admin',])->prefix('super-admin')->group(function () {
    Route::get('/dashboard', [SuperAdminController::class, 'dashboard']);

});


Route::middleware(['auth:sanctum', 'role:customer',])->prefix('customer')->group(function () {
    Route::get('/dashboard',[CustomerController::class, 'dashboard']);

    Route::get('/services', [CustomerServiceController::class, 'index']);
    Route::get('/services/{service}', [CustomerServiceController::class, 'show']);

    Route::get('/countries', [LocationController::class, 'countries']);
    Route::get('/countries/{country}/regions', [LocationController::class, 'regions']);

    //Route::get('/services/{service}/document-requirements',[CustomerServiceDocumentRequirementController::class,'index']);
    Route::get('/service-document-requirements',[CustomerServiceDocumentRequirementController::class, 'index']);

    Route::get('/lawyers', [CustomerLawyerController::class, 'index']);

    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class,'index']);
        Route::post('/', [OrderController::class, 'store']);
        Route::get('/{order}', [OrderController::class, 'show']);
        Route::patch('/{order}/cancel', [OrderController::class, 'cancel']);
    });

});

Route::middleware(['auth:sanctum', 'role:apostille-officer',])->prefix('apostille-officer')->group(function () {
    Route::get('/dashboard',[ApostilleOfficerController::class, 'dashboard']);

    Route::get('/profile', [ LawyerProfileController::class, 'me' ]); 
    Route::post('/profile', [ LawyerProfileController::class, 'storeOrUpdate' ]); 

    Route::get('/documents', [ LawyerDocumentController::class, 'index' ]); 
    Route::post('/documents', [ LawyerDocumentController::class, 'store' ]); 
    Route::delete('/documents/{document}', [ LawyerDocumentController::class, 'destroy' ]); 

    Route::get('/service-regions', [ LawyerServiceRegionController::class, 'index' ]); 
    Route::post('/service-regions', [ LawyerServiceRegionController::class, 'store' ]); 
    Route::put('/service-regions/{serviceRegion}', [ LawyerServiceRegionController::class, 'update' ]); 
    Route::delete('/service-regions/{serviceRegion}', [ LawyerServiceRegionController::class, 'destroy' ]);

    Route::get('/services', [ServiceController::class,'index']);
    Route::get( '/countries', [CountryController::class, 'index'] ); 
    Route::get( 'countries/{country}', [CountryController::class, 'show'] ); 

    Route::prefix('service-pricings')->group(function () {
        Route::get('/', [LawyerServicePricingController::class, 'index']);
        Route::post('/', [LawyerServicePricingController::class, 'store']);
        Route::put('/{pricing}', [LawyerServicePricingController::class, 'update']);
        Route::delete('/{pricing}', [LawyerServicePricingController::class, 'destroy']);
    });


});


Route::middleware(['auth:sanctum','role:business-client',])->prefix('business')->group(function () {
    Route::get('/dashboard', [BusinessController::class, 'dashboard']);
});


Route::middleware(['auth:sanctum','role:finance',])->prefix('finance')->group(function () {
    Route::get('/dashboard', [FinanceController::class, 'dashboard']);
});

Route::middleware(['auth:sanctum','role:courier',])->prefix('courier')->group(function () {
    Route::get('/dashboard', [CourierController::class, 'dashboard']);
});

Route::middleware(['auth:sanctum','role:customer-support',])->prefix('support')->group(function () {
    Route::get('/dashboard', [SupportController::class, 'dashboard']);
});