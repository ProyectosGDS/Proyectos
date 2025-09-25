<?php

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
Route::get('/auth/csrf-cookie', function (Request $request) {
    return response('');
});
