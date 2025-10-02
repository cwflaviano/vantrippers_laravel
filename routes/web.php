<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Test route to verify Phiki syntax highlighting works in error pages
Route::get('/test-error', function () {
    throw new Exception('Test error to check if Phiki syntax highlighting is working properly in Laravel error pages');
});
