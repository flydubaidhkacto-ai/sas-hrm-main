<?php
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectCategoryController;
use App\Http\Controllers\TaskCategoryController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TimeTrackerController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TaskStatusController;
use Illuminate\Support\Facades\Route;



Route::resource('clients', ClientController::class);
Route::resource('project-categories', ProjectCategoryController::class);

Route::resource('projects', ProjectController::class);
Route::patch('/projects/{project}/update-status', [ProjectController::class, 'updateStatus'])->name('projects.update-status');
Route::post('/tasks/update-status-kanban', [ProjectController::class, 'updateStatusKanban'])->name('tasks.update.status.kanban');

Route::resource('task-categories', TaskCategoryController::class);
Route::resource('tasks', TaskController::class);

Route::patch('/tasks/{task}/update-status', [TaskController::class, 'updateStatus'])->name('tasks.update.status');

// Route::resource('time-trackers', TimeTrackerController::class);

Route::post('/tasks/{task}/start-timer', [TimeTrackerController::class, 'startTimer']);
Route::post('/tasks/{task}/push-timer', [TimeTrackerController::class, 'pushTimer']);
Route::post('/tasks/{task}/stop-timer', [TimeTrackerController::class, 'stopTimer']);
Route::get('/time-trackers/timesheet', [TimeTrackerController::class, 'timesheet'])->name('time-trackers.timesheet');

Route::resource('tickets', TicketController::class);
Route::post('/tickets/{ticket}/reply', [TicketController::class, 'reply'])->name('tickets.reply.store');

Route::patch('/tickets/{ticket}/status', [TicketController::class, 'updateStatus'])->name('tickets.update.status');

// Task Status
// Route::resource('task-status', TaskStatusController::class);
Route::get('/task-status', [TaskStatusController::class, 'index'])->name('task-status.index');
Route::post('/task-status/store', [TaskStatusController::class, 'store'])->name('task-status.store');
Route::put('/task-status/update', [TaskStatusController::class, 'update'])->name('task-status.update');
Route::delete('/task-status/delete', [TaskStatusController::class, 'destroy'])->name('task-status.destroy');
