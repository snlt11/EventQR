<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FormController;
use App\Http\Controllers\ParticipantController;

// Form Routes
Route::get('/form-builder', function () {return view('form-builder');})->name('form.builder');
Route::get('/form/{id}', [FormController::class, 'showPublishedForm']);
Route::post('/publish-form', [FormController::class, 'publishForm'])->name('form.publish');
Route::post('/submit-form/{id}', [FormController::class, 'submitForm']);

Route::get('/download-info', [ParticipantController::class, 'participantDownloadInformation'])->name('download.info');