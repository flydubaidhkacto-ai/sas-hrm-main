<?php

use App\Http\Controllers\RosterController;
use Illuminate\Support\Facades\Route;

Route::get('roster', [RosterController::class, 'index'])->name('roster.index');
Route::get('roster/create', [RosterController::class, 'create'])->name('roster.create');
Route::get('roster/edit/{id}', [RosterController::class, 'edit'])->name('roster.edit');
Route::post('roster/store', [RosterController::class, 'store'])->name('roster.store');
Route::post('roster/update/{id}', [RosterController::class, 'update'])->name('roster.update');
Route::get('roster/delete/{id}', [RosterController::class, 'delete'])->name('roster.delete');
Route::get('/print-roster/{id}', [RosterController::class, 'print'])->name('roster.print');
Route::post('/publish-draft/{roster}', [RosterController::class, 'publish_draft'])->name('roster.publish_draft');
Route::get('/roster/draft', [RosterController::class, 'index'])->name('roster.draft');
Route::get('/my-roster', [RosterController::class, 'my'])->name('roster.my');
Route::get('/copy/{id}', [RosterController::class, 'copy'])->name('roster.copy');
Route::get('/print-rosters', [RosterController::class, 'printFiltered'])->name('roster.print.filtered');


