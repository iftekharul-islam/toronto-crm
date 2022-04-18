<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\WebApiController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\LocalizationController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get( '/locale/{locale}', LocalizationController::class )->name( 'locale.change' );
Route::group( [ 'middleware' => [ 'auth:sanctum' ] ], function () {
    //Dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    //User Controller
    Route::get( 'users',
        [ UserController::class, 'index' ] )->middleware( 'role_permission:view.user' )->name( 'users.index' );
    Route::get( 'users/create',
        [ UserController::class, 'create' ] )->middleware( 'role_permission:create.user' )->name( 'users.create' );
    Route::post( 'users',
        [ UserController::class, 'store' ] )->middleware( 'role_permission:create.user' )->name( 'users.store' );
    Route::get( 'users/{id}/edit',
        [ UserController::class, 'edit' ] )->middleware( 'role_permission:update.user' )->name( 'users.edit' );
    Route::put( 'users/{id}',
        [ UserController::class, 'update' ] )->middleware( 'role_permission:update.user' )->name( 'users.update' );
    Route::post( 'users/{id}',
        [ UserController::class, 'destroy' ] )->middleware( 'role_permission:delete.user' )->name( 'users.destroy' );
    //Role Controller
    Route::get( 'roles',
        [ RoleController::class, 'index' ] )->middleware( 'role_permission:view.role' )->name( 'roles.index' );
    Route::get( 'roles/create',
        [ RoleController::class, 'create' ] )->middleware( 'role_permission:create.role' )->name( 'roles.create' );
    Route::post( 'roles',
        [ RoleController::class, 'store' ] )->middleware( 'role_permission:create.role' )->name( 'roles.store' );
    Route::get( 'roles/{id}/edit',
        [ RoleController::class, 'edit' ] )->middleware( 'role_permission:update.role' )->name( 'roles.edit' );
    Route::put( 'roles/{id}',
        [ RoleController::class, 'update' ] )->middleware( 'role_permission:update.role' )->name( 'roles.update' );
    Route::post( 'roles/{id}',
        [ RoleController::class, 'destroy' ] )->middleware( 'role_permission:delete.role' )->name( 'roles.destroy' );
    Route::resource('clients', ClientController::class);
} );
Auth::routes();
Route::redirect('/', '/login');
Route::get('model-names', [RoleController::class, 'getModels']);
Route::view( '/order', 'dashboard.order' );
Route::view( '/order-details', 'dashboard.order-details' )->name( 'order.details' );
Route::view( '/order-add', 'dashboard.order-add' )->name( 'order.add' );
Route::view( '/order-add-step2', 'dashboard.order-add-step2' )->name( 'order.add-step2' );
Route::view( '/clients', 'dashboard.clients' );
Route::view( '/client-view', 'dashboard.client-view' )->name( 'client.view' );
Route::view( '/client-add', 'dashboard.client-add' )->name( 'client.add' );
Route::get( 'get/icons', function () {
    return view( 'icon' );
} );
Route::get( 'email/verify/{id}/{hash}', [ VerificationController::class, 'verify' ] )->name( 'verification.verify' );
Route::get( 'accept-new-user/{email}', [ UserController::class, 'acceptInviteUser' ] )->name( 'accept.new.user' );
Route::get( "{slug}", [ WebApiController::class, 'home' ] )->where( 'slug', ".*" );

//vue routes
//Route::get("{slug}", [WebApiController::class, 'home'])->where('slug', ".*");
