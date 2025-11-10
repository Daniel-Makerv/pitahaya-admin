<?php

use App\Models\Form;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect('/login');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('forms', function () {
    dd(Form::get());
    // return Inertia::render('forms');
})->middleware(['auth', 'verified'])->name('forms');

require __DIR__.'/settings.php';
