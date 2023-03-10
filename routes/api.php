<?php

use App\Http\Controllers\App\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/', function() {
    return response()->json(['status' => 'OK']);
});

Route::post('register', [AuthController::class, 'register'])->name('user.register');
Route::post('login', [AuthController::class, 'login'])->name('user.login');
Route::post('refresh_token', [AuthController::class, 'refreshToken'])->name('user.refresh_token');

Route::middleware('auth:api')->group(function() {
    Route::post('logout', [AuthController::class, 'logout'])->name('user.logout');
    Route::get('profile', [AuthController::class, 'profile'])->name('user.profile');
});
