<?php
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;


Route::controller( StudentController::class)->group( function () {

    Route::prefix('students')->name('students.')->group( function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::post('store', 'store')->name('store');
        Route::get('{student}', 'show')->name('show');
        Route::get('{student}/edit', 'edit')->name('edit');
        Route::put('{student}', 'update')->name('update');
        Route::get('{student}/delete', 'destroy')->name('delete');
    });
    Route::prefix('attendance')->name('attendance.')->group( function () {
        Route::get('/students', 'attendance')->name('students.show');
    });

    /**
     * Classes Routes
     */
    Route::prefix('classes')->name('classes.')->group(function () {
        Route::get('/', 'classesIndex')->name('index');
        Route::post('save', 'saveRecordClasses')->name('save');
        Route::post('update', 'updateRecordClasses')->name('update');
        Route::delete('delete', 'deleteRecordClasses')->name('delete');
        Route::post('modal/save', 'saveModalClasses')->name('modal.save');
    });
});

