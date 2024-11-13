<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect(to: route(name: 'filament.app.auth.login'));
});

Route::middleware('auth')
    ->get(uri: '/download/{file}', action: function (string $file) {
        return \Illuminate\Support\Facades\Storage::download($file);
    })
    ->name(name: 'download.zip');

/*Route::get('/render/{invoice}', function ($invoice) {
    return view('pdf.HN_invoice', ['invoice' => \App\Models\Invoice::find($invoice)]);
});*/
