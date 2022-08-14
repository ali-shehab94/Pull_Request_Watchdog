<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RequestController;

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

Route::get('/test', [RequestController::class, 'test']);
Route::get('/fetch', [RequestController::class, 'fetch']);
Route::get('/open_repos', [RequestController::class, 'allOpenRepos14']);
Route::get('/reviews', [RequestController::class, 'reviews']);
