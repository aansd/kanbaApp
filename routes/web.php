<?php

use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('home');
})->name('home');

//group tasks route
Route::prefix('tasks')
    ->name('tasks.')
    ->controller(TaskController::class)
    ->group(function (){
    Route::get('/', 'index')->name('index');
    Route::get('/create', 'create')->name('create');
    Route::post('/', 'store')->name('store');
    Route::get('/{id}/edit', 'edit')->name('edit');
    Route::put('/{id}', 'update')->name('update');
    Route::get('/{id}/delete', 'delete')->name('delete');
    Route::delete('/{id}', 'destroy')->name('destroy');
    Route::get('progress', 'progress')->name('progress');
    Route::patch('/{id}/move', 'move')->name('move');
});


// route basic
// Route::get('/tasks/', [TaskController::class, 'index'])->name('tasks.index');
// Route::get('/tasks/create', [TaskController::class, 'create'])->name('tasks.create');
// Route::get('/tasks/{id}/edit', [TaskController::class, 'edit'])->name('tasks.edit');
