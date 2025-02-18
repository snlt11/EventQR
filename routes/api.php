<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\GenerateReferralCodeController;
use App\Http\Controllers\ParticipantController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/events/{eventId}', [EventController::class, 'submitEventForm']);

Route::middleware('auth:api')->group(function () {

    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/generate-referral-code', GenerateReferralCodeController::class);

    // Event routes
    Route::post('/event', [EventController::class, 'createEvent']);
    Route::get('/events', [EventController::class, 'listEvents']);
    Route::post('/event/{eventId}/toggle-publish', [EventController::class, 'toggleEventPublishStatus']);

    // Participant routes
    Route::post('/validate', [ParticipantController::class, 'validateQRAndAuth']);
    Route::get('/participants/{eventId}', [ParticipantController::class, 'listOfParticipants']);
    Route::post('/participant/update-status', [ParticipantController::class, 'updateParticipantStatus']);
    Route::post('/event/{eventId}/change-status/{status}', [EventController::class, 'changeEventParticipantStatus']);
});
