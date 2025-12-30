<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BlueSkyController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| These routes handle the BlueSky post creation flow.
| The goal is to provide a simple UI where a user can
| create and publish a post to BlueSky using the AT Protocol.
|
| Authentication is handled server-side using a BlueSky
| App Password. No credentials or tokens are exposed
| to the frontend.
|--------------------------------------------------------------------------
*/

/**
 * Default welcome route (Laravel default)
 */
Route::get('/', function () {
    return view('welcome');
});

/**
 * Show the BlueSky post creation form
 *
 * GET /post
 * - Renders the UI where user can write text and upload an image
 */
Route::get('/post', [BlueSkyController::class, 'index']);

/**
 * Handle BlueSky post submission
 *
 * POST /post
 * - Creates a BlueSky session (access token)
 * - Uploads image (if provided)
 * - Publishes the post to BlueSky
 * - Uses a stateless approach (no token storage)
 */
Route::post('/post', [BlueSkyController::class, 'store'])->name('bluesky.post');
