<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ChatController;

Route::post('/send-message', [ChatController::class, 'sendMessage']);
Route::get('/', function () {
    return view('chat');
});
