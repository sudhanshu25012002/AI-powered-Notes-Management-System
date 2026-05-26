<?php

use App\Http\Controllers\Web\NoteWebController;
use Illuminate\Support\Facades\Route;

// Redirect root to notes
Route::get('/', fn () => redirect('/notes'));

// Blade UI routes
Route::get('/notes',              [NoteWebController::class, 'index'])->name('notes.index');
Route::get('/notes/create',       [NoteWebController::class, 'create'])->name('notes.create');
Route::get('/notes/{id}',         [NoteWebController::class, 'show'])->name('notes.show');
Route::get('/notes/{id}/edit',    [NoteWebController::class, 'edit'])->name('notes.edit');

