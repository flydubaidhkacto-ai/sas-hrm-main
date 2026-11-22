<?php
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

// Notification Controllar
Route::get('/notification/index', [NotificationController::class, 'index'])->name('notification.index');
Route::get('/notification/input', [NotificationController::class, 'create'])->name('notification.create');
Route::post('/notification/save', [NotificationController::class, 'store'])->name('notification.save');
Route::put('/notification/update', [NotificationController::class, 'update'])->name('notification.update');
Route::delete('/notification/delete', [NotificationController::class, 'destroy'])->name('notification.delete');
Route::get('/notification/view/{id}', [NotificationController::class, 'show'])->name('notification.show');
Route::get('/notification/markallread/{id}', [NotificationController::class, 'mark_all_read']);
Route::get('/notification/vanish/{id}/{user}', [NotificationController::class, 'vanish']);
Route::get('/notification/edit/{id}', [NotificationController::class, 'edit'])->name('notification.edit');
Route::get('/my_notification', [NotificationController::class, 'my_notification']);
Route::get('/notification/markalldeleted/{id}', [NotificationController::class, 'mark_all_deleted'])->name('notification.mark_all_deleted');


