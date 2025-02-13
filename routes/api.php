<?php

use App\Http\Controllers\ParticipantController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/checkin/{token}', [ParticipantController::class, 'participantCheckIn']);