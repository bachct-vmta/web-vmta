<?php

use Illuminate\Support\Facades\Route;
use Packages\Inquiry\Src\Http\Controllers\Public\ContactController;
use Packages\Inquiry\Src\Http\Controllers\Public\EmergencyController;
use Packages\Inquiry\Src\Http\Controllers\Public\QuickInquiryController;
use Spatie\Honeypot\ProtectAgainstSpam;

/*
| Routes are mounted by InquiryServiceProvider inside the locale prefix group.
| GET pages render forms; POST endpoints share the inquiry.throttle bucket and
| Spatie's ProtectAgainstSpam middleware (honeypot).
*/

$throttle = 'throttle:'.config('inquiry.throttle', '5,1');

Route::get('/contact', [ContactController::class, 'show'])->name('contact.show');
// Route::get('/khan-cap', [EmergencyController::class, 'show'])->name('emergency.show');

Route::middleware([ProtectAgainstSpam::class, $throttle])->group(function () {
    Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');
    // Route::post('/khan-cap', [EmergencyController::class, 'store'])->name('emergency.store');
    Route::post('/inquiry/quick', [QuickInquiryController::class, 'store'])->name('quick.store');
});
