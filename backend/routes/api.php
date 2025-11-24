<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\AdminEventController;
use App\Http\Controllers\Api\AdminEventParticipantsController;

Route::get('/ping', fn () => response()->json(['message' => 'pong']));

Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/register', [AuthController::class, 'register'])->name('register');

Route::get('/events', [EventController::class, 'index'])->name('events.index');
Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/me', [AuthController::class, 'me'])->name('me');

    Route::post('/events/{event}/bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::delete('/bookings/{booking}', [BookingController::class, 'destroy'])->name('bookings.destroy');

    Route::post('/admin/events', [AdminEventController::class, 'store'])->name('admin.events.store');
    Route::put('/admin/events/{event}', [AdminEventController::class, 'update'])->name('admin.events.update');
    Route::delete('/admin/events/{event}', [AdminEventController::class, 'destroy'])->name('admin.events.destroy');

    Route::get('/admin/events/{event}/participants', [AdminEventParticipantsController::class, 'index'])
        ->name('admin.events.participants.index');

    Route::get('/admin/events/{event}/participants/export', [AdminEventParticipantsController::class, 'export'])
        ->name('admin.events.participants.export');
});
