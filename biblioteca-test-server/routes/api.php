<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use ThreeLeaf\Biblioteca\Http\Controllers\Api\AuthorController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('authors', [AuthorController::class, 'index'])->name('authors.index');
Route::get('authors/{author_id}', [AuthorController::class, 'show'])->name('authors.show');

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::post('authors', [AuthorController::class, 'store'])->name('authors.store');
    Route::put('authors/{author_id}', [AuthorController::class, 'update'])->name('authors.update');
    Route::delete('authors/{author_id}', [AuthorController::class, 'destroy'])->name('authors.destroy');
});
