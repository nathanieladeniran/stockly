<?php

use App\Http\Controllers\Api\v1\OnboardingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
Route::get('/', function () {
    return response()->json(['message' => 'Welcome to Stockly API']);
});

Route::get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/', function () {
    return response()->json(['message' => 'Welcome to Laravel']);
});


// Define other API routes here

//Authentication and onboarding endpoints
Route::prefix('v1/auth')->namespace('Api\v1')->group(function () {
    Route::post('/register', [OnboardingController::class, 'addNewUser'])->name('registration');
});

Route::fallback(function (Request $request) {
    return response()->json([
        'message' => 'Record not found.'
    ], 404);
});