<?php

use App\Http\Controllers\PromotionController;
use Illuminate\Support\Facades\Route;

//Promotion Controller
Route::get('/promotion/apply/{id}', [PromotionController::class, 'apply'])->name('promotion.apply');
Route::post('/promotion/apply/save', [PromotionController::class, 'store'])->name('promotion.apply.save');
Route::get('/promotion/requested', [PromotionController::class, 'requested'])->name('promotion.requested');
Route::get('/promotion/pending', [PromotionController::class, 'pending'])->name('promotion.pending');
Route::get('/promotion/approve/{id}', [PromotionController::class, 'approve'])->name('promotion.approve');
Route::get('/promotion/reject/{id}', [PromotionController::class, 'reject'])->name('promotion.reject');
Route::get('/promotion/final_list', [PromotionController::class, 'finale_list'])->name('promotion.final.list');
Route::get('/promotion/finale/approve/{id}', [PromotionController::class, 'finale_approve'])->name('finale.promotion.approve');
Route::get('/promotion/finale/reject/{id}', [PromotionController::class, 'finale_reject'])->name('finale.promotion.reject');
Route::get('/promotion/select', [PromotionController::class, 'select'])->name('promotion.select');
Route::post('/promotion/delete', [PromotionController::class, 'destroy'])->name('promotion.delete');


