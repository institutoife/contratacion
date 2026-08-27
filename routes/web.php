<?php

use App\Http\Controllers\ApplicantController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\InterviewSlotController;
use App\Http\Controllers\PublicApplicationController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicApplicationController::class, 'index'])->name('welcome');
Route::post('/applications', [PublicApplicationController::class, 'store'])->name('applications.store');
Route::get('/applications/{applicant}/print', [PublicApplicationController::class, 'print'])
    ->middleware('signed')
    ->name('applications.print');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::redirect('/panel', '/applicants');

    Route::get('/reports/applicants-by-position', [ApplicantController::class, 'positionReportPdf'])
        ->name('applicants.position-report');
    Route::resource('applicants', ApplicantController::class);
    Route::get('/applicants/{applicant}/print', [ApplicantController::class, 'printProfile'])->name('applicants.print');
    Route::patch('/applicants/{applicant}/quick-update', [ApplicantController::class, 'quickUpdate'])->name('applicants.quick-update');
    Route::post('/applicants/{applicant}/interviews', [ApplicantController::class, 'storeInterview'])->name('applicants.interviews.store');
    Route::get('/applicants-export', [ApplicantController::class, 'export'])->name('applicants.export');

    Route::get('/attachments/{attachment}/download', [ApplicantController::class, 'downloadAttachment'])->name('attachments.download');
    Route::delete('/attachments/{attachment}', [ApplicantController::class, 'destroyAttachment'])->name('attachments.destroy');

    Route::patch('/positions/{position}/toggle', [ApplicantController::class, 'togglePosition'])->name('positions.toggle');
    Route::post('/positions', [ApplicantController::class, 'storePosition'])->name('positions.store');
    Route::put('/positions/{position}', [ApplicantController::class, 'updatePosition'])->name('positions.update');
    Route::delete('/positions/{position}', [ApplicantController::class, 'destroyPosition'])->name('positions.destroy');

    Route::post('/interview-slots', [InterviewSlotController::class, 'store'])->name('interview-slots.store');
    Route::get('/interview-slots/{interviewSlot}/report', [ApplicantController::class, 'interviewSlotReportPdf'])->name('interview-slots.report');
    Route::patch('/interview-slots/{interviewSlot}/toggle', [InterviewSlotController::class, 'toggle'])->name('interview-slots.toggle');
    Route::delete('/interview-slots/{interviewSlot}', [InterviewSlotController::class, 'destroy'])->name('interview-slots.destroy');
});
