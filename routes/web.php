<?php
use Illuminate\Support\Facades\Route;
use USO\SsoClient\Http\Controllers\CallbackController;

Route::get('/sso/callback', [CallbackController::class, 'handle'])->name('sso.callback');
Route::get('/sso/redirect', function(){
    return app(\USO\SsoClient\Services\SsoClient::class)->redirectToSso();
})->name('sso.redirect');
