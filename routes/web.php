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
use App\Http\Controllers\OrderController;

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
	 Route::get( 'dashboard', [ DashboardController::class, 'index' ] )->name( 'dashboard' );
	 //User Controller
	 Route::get( 'users',
		 [ UserController::class, 'index' ] )->middleware( 'role_permission:view.user' )->name( 'users.index' );
	 Route::get( 'users/create',
		 [ UserController::class, 'create' ] )->middleware( 'role_permission:create.user' )->name( 'users.create' );
	 Route::post( 'users',
		 [ UserController::class, 'store' ] )->middleware( 'role_permission:create.user' )->name( 'users.store' );
	 Route::post( 'user-status-change/{id}', [
		 UserController::class,
		 'statusChange',
	 ] )->middleware( 'role_permission:update.user' )->name( 'users.status.change' );
	 Route::get( 'users/{id}/edit',
		 [ UserController::class, 'edit' ] )->middleware( 'role_permission:update.user' )->name( 'users.edit' );
	 Route::put( 'users/{id}',
		 [ UserController::class, 'update' ] )->middleware( 'role_permission:update.user' )->name( 'users.update' );
	 Route::post( 'users/{id}',
		 [ UserController::class, 'destroy' ] )->middleware( 'role_permission:delete.user' )->name( 'users.destroy' );
	 Route::get( 'profiles', [ UserController::class, 'getProfile' ] )->name( 'profile' );
	 Route::post( 'profiles', [ UserController::class, 'updateProfile' ] )->name( 'profile.update' );
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
	 //Client Controller
	 Route::get( 'clients',
		 [ ClientController::class, 'index' ] )->middleware( 'role_permission:view.client' )->name( 'clients.index' );
	 Route::get( 'clients/create',
		 [ ClientController::class, 'create' ] )->middleware( 'role_permission:create.client' )->name( 'clients.create' );
	 Route::post( 'clients',
		 [ ClientController::class, 'store' ] )->middleware( 'role_permission:create.client' )->name( 'clients.store' );
	 Route::get( 'clients/{id}',
		 [ ClientController::class, 'show' ] )->middleware( 'role_permission:view.client' )->name( 'clients.show' );
	 Route::get( 'clients/{id}/edit',
		 [ ClientController::class, 'edit' ] )->middleware( 'role_permission:update.client' )->name( 'clients.edit' );
	 Route::put( 'clients/{id}',
		 [ ClientController::class, 'update' ] )->middleware( 'role_permission:update.client' )->name( 'clients.update' );
	 Route::delete( 'clients/{id}',
		 [ ClientController::class, 'destroy' ] )->middleware( 'role_permission:delete.client' )->name( 'clients.destroy' );
	 Route::get( 'get-clients/{type}', [ ClientController::class, 'getClientsByType' ] );
	 //order
//	 Route::resource( 'orders', OrderController::class );
	 Route::get( 'orders',
		 [ OrderController::class, 'index' ] )->middleware( 'role_permission:view.order' )->name( 'orders.index' );
	 Route::get( 'orders/create',
		 [ OrderController::class, 'create' ] )->middleware( 'role_permission:create.order' )->name( 'orders.create' );
	 Route::post( 'orders',
		 [ OrderController::class, 'store' ] )->middleware( 'role_permission:create.order' )->name( 'orders.store' );
	 Route::get( 'orders/{id}',
		 [ OrderController::class, 'show' ] )->middleware( 'role_permission:view.order' )->name( 'orders.show' );
	 Route::get( 'orders/{id}/edit',
		 [ OrderController::class, 'edit' ] )->middleware( 'role_permission:update.order' )->name( 'orders.edit' );
	 Route::put( 'orders/{id}',
		 [ OrderController::class, 'update' ] )->middleware( 'role_permission:update.order' )->name( 'orders.update' );
	 Route::delete( 'orders/{id}',
		 [ OrderController::class, 'destroy' ] )->middleware( 'role_permission:delete.order' )->name( 'orders.destroy' );
} );
Auth::routes();
Route::get('/file-import',[ClientController::class,'importClient'])->name('import-client');
Route::post('/import',[ClientController::class,'import'])->name('import');
Route::redirect( '/', '/login' );
Route::view( '/404', 'dashboard.error' );
Route::view( '/order', 'dashboard.order' );
Route::view( '/order-details', 'dashboard.order-details' )->name( 'order.details' );
Route::view( '/order-add', 'dashboard.order-add' )->name( 'order.add' );
Route::view( '/order-add-step2', 'dashboard.order-add-step2' )->name( 'order.add-step2' );
Route::get( 'get/icons', function () {
	 return view( 'icon' );
} );
Route::get( 'email/verify/{id}/{hash}', [ VerificationController::class, 'verify' ] )->name( 'verification.verify' );
Route::get( 'accept-new-user/{code}', [ UserController::class, 'acceptInviteUser' ] )->name( 'accept.new.user' );
Route::post( 'invite-user-update/{id}',
	[ UserController::class, 'inviteUserUpdate' ] )->name( 'update.invite.user.profile' );
//Route::get( "{slug}", [ WebApiController::class, 'home' ] )->where( 'slug', ".*" );
