<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect(to: route(name: 'filament.app.auth.login'));
});
