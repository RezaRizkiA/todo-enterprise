<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\TodosController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('home');

Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {
    Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/todos', [TodosController::class, 'index'])->name('todos.index');
    Route::post('/todos', [TodosController::class, 'store'])->name('todos.store');

    // base endpoints biar composable gampang
    Route::patch('/todos/update/{id}', [TodosController::class, 'update'])->name('todos.update.base');
    Route::patch('/todos/toggle/{id}', [TodosController::class, 'toggle'])->name('todos.toggle.base');
    Route::delete('/todos/destroy/{id}', [TodosController::class, 'destroy'])->name('todos.destroy.base');
});


require __DIR__ . '/auth.php';
