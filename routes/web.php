<?php

use App\Http\Controllers\LinkedinConnectionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/linkedin-connections', [LinkedinConnectionController::class, 'index'])
    ->name('linkedin-connections.index');

Route::post('/linkedin-connections', [LinkedinConnectionController::class, 'store'])
    ->name('linkedin-connections.store');
