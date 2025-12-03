<?php

namespace Vendor\SsoClient\Http\Middleware;

use Closure;
use Illuminate\Support\Str;

class RedirectIfNotSsoAuthenticated
{
    public function handle($request, Closure $next)
    {
        if (auth()->check()) {
            return $next($request);
        }

        $state = Str::random(40);

        session()->put(config('sso.state_key'), $state);

        $sso = app(\USO\SsoClient\Services\SsoClient::class);

        return redirect()->away($sso->redirectToSso());
    }
}
