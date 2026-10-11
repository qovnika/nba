<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
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

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:sanctum'])->get('/getTokens', function (Request $request) {
    return $request;
});

//Get user using email address
Route::post("/getUser", [RegisteredUserController::class, "getUser"]);

// Get the currently logged in user
Route::middleware('auth:sanctum')->get('/currentUser', function (Request $request) {
    return $request->user();
});

// Get the currently logged in user using post request
Route::middleware('auth:sanctum')->post('/currentUser', function (Request $request) {
    return ["status" => true, "message" => "Successfully retrieved user details.", "data" => $request->user()];
});

// Get all users in the system
Route::get('/getUsers', [UserController::class, "index"]);

// Get users from the system using a search term
Route::post('/searchUsers', [UserController::class, "index"]);

// Get user with ID
Route::middleware('auth:sanctum')->get('/getUser/{id}', [UserController::class, "index"]);

//Update user profile
Route::post("/updateUser", [UserController::class, "update"]);

//Update user profile from admin
Route::post("/adminUpdateUser", [UserController::class, "update"]);

//Update user password
Route::post("/updatePassword", [UserController::class, "updatePassword"]);

//Delete user
Route::post("/deleteUser", [UserController::class, "destroy"]);

// Upload file
Route::post('/uploadFile', [UserController::class, "uploadFile"]);

// Upload Base64 file
Route::post('/uploadMessageFile', [UserController::class, "uploadMessageFile"]);

//Create new payment/title
Route::post("createTitle", [TitleController::class, "store"]);

//Verify Email via code
Route::post("verifyEmail", [EmailVerificationNotificationController::class, "verifyEmail"]);

//Get Titles
Route::get("getTitles", [TitleController::class, "index"]);

//Update title
Route::post("updateTitle", [TitleController::class, "update"]);

//Store Subscription
Route::post("storeSubscription", [SubscriptionController::class, "store"]);

//Get Subscriptions
Route::post("getSubscriptions", [SubscriptionController::class, "index"]);

//Get Flutterwave link
Route::post("/getFlutterwaveLink", function (Request $request) {
    $response = Http::withHeaders([
        "Content-Type" => "application/json",
        "Accept" => "application/json",
        "Authorization" => "Bearer FLWSECK-c5ecccc61258226b58bc50f8c3ae4476-1946016bf29vt-X"
    ])->withToken($request->token)->post(
        'https://api.flutterwave.com/v3/payments',
        $request
    );

    return $response;
});

//Forgot password reset
Route::post("forgotPassword", [UserController::class, "forgotPassword"]);

//Forgot password reset
Route::post("searchTransactions", [SubscriptionController::class, "searchTransactions"]);

//Send email verification code
Route::get("sendEmailVerificationMail", [UserController::class, "sendEmailVerificationMail"]);

//Resend email verification code
Route::post("resendEmailVerificationMail", [UserController::class, "resendEmailVerificationMail"]);

//Verify Flutterwave Transaction (stores subscription with all passed variables when successful)
Route::post("verifyFlutterwaveTransaction", function (Request $request) {
    $request->validate([
        'id' => 'required',
        'user_id' => 'nullable|exists:users,id',
        'due' => 'nullable|numeric',
        'registration' => 'nullable|numeric',
        'tx_ref' => 'nullable|string',
    ]);

    $response = Http::withHeaders([
        "Content-Type" => "application/json",
        "Accept" => "application/json",
        "Authorization" => "Bearer " . (env('FLUTTERWAVE_SECRET_KEY') ?? 'FLWSECK-c5ecccc61258226b58bc50f8c3ae4476-1946016bf29vt-X'),
    ])->get(
        'https://api.flutterwave.com/v3/transactions/'.$request->id."/verify"
    );

    $body = $response->json();
    $data = $body['data'] ?? null;
    $userId = $request->user_id;

    if ($response->successful() && $data && strtolower($data['status'] ?? '') === 'successful' && $userId) {
        $txRef = $data['tx_ref'] ?? $request->tx_ref ?? (string) $data['id'];
        $payload = [
            'user_id' => $userId,
            'amount' => (float) ($data['amount'] ?? 0),
            'status' => 'Successful',
        ];
        if ($request->has('due')) {
            $payload['due'] = (float) $request->due;
        }
        if ($request->has('registration')) {
            $payload['registration'] = (float) $request->registration;
        }
        \App\Models\Subscription::updateOrCreate(
            ['tx_ref' => $txRef],
            $payload
        );
    }

    return $response;
});

// Flutterwave webhook (for transfer/completed payments)
Route::post("flutterwave/webhook", [FlutterwaveWebhookController::class, "handle"]);