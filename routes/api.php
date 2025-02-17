<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\GenerateReferralCodeController;
use App\Http\Controllers\ParticipantController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:api')->group(function () {

    // Public Routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/events/{eventId}', [EventController::class, 'submitEventForm']);


    Route::middleware('auth:api')->group(function () {
        Route::get('/user', function (Request $request) {
            return $request->user();
        });
        Route::post('/generate-referral-code', GenerateReferralCodeController::class);
        Route::post('/logout', [AuthController::class, 'logout']);

        // Event routes
        Route::post('/event', [EventController::class, 'createEvent']);
        Route::get('/events', [EventController::class, 'listEvents']);
        Route::post('/event/{eventId}/toggle-publish', [EventController::class, 'toggleEventPublishStatus']);
        Route::post('/event/{eventId}/change-status/{status}', [EventController::class, 'changeEventStatus']);

        // Participant routes
        Route::post('/participant/update-status', [ParticipantController::class, 'updateParticipantStatus']);
        Route::post('/validate', [ParticipantController::class, 'validateQRAndAuth']);
    });


    Route::any('{any}', function () {
        throw new App\Helper\MessageError('Not Found. If you are having trouble, please contact support.', 404);
    })->where('any', '.*');

});
