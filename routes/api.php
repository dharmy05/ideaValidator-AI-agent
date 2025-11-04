<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\A2AController;

Route::post('/a2a/message', [A2AController::class, 'handle']);
