<?php
use Illuminate\Support\Facades\Route;
use USO\SsoClient\Http\Controllers\CallbackController;

Route::get('/sso/callback', [CallbackController::class, 'handle'])->name('sso.callback');
Route::get('/sso/redirect', function(\USO\SsoClient\Services\SsoClient $ssoClient) {

    return $ssoClient->redirectToSso();

})->name('sso.redirect');
