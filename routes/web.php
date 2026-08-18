<?php

use App\Http\Controllers\ReorderTasksController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::resource('tasks', TaskController::class)->only(['index', 'store', 'update', 'destroy']);
Route::post('/tasks/reorder', ReorderTasksController::class)->name('tasks.reorder');

Route::redirect('/', '/tasks');
