<?php

use App\Livewire\Auth\{Login, Register};
use App\Livewire\Welcome;
use Illuminate\Support\Facades\{Auth, Route};

Route::get('/login', Login::class)->name('login');
Route::get('/register', Register::class)->name('register');
Route::get('/logout', fn () => Auth::logout());

Route::middleware('auth')->group(function () {
    Route::get('/', Welcome::class)->name('dashboard');
});
