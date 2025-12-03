<?php

namespace USO\SsoClient\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use USO\SsoClient\Services\SsoClient;
use Illuminate\Support\Facades\Auth;

class CallbackController extends Controller
{
    public function __construct(protected SsoClient $ssoClient){}

    public function handle(Request $request)
    {
        $state = session()->pull(config('sso.state_key'));
        if (!$state || $state !== $request->get('state')) {
            abort(403, 'Invalid SSO state');
        }
        if ($request->has('error')) {
            return redirect('/')->with('error', $request->get('error_description') ?: $request->get('error'));
        }

        $code = $request->get('code');

        try {

            $resp = Http::withToken($this->ssoClient->getAccessToken())->get(rtrim(config('sso.server_url'),'/').config('sso.exchange_endpoint'), [
                'code' => $code,
            ]);

        } catch (\Throwable $exception) {
            Log::error('Не удалось получить токен', ['exception' => $exception]);
        }

        if ($resp->failed()) {
            abort(403, 'Token exchange failed');
        }

        $userData = $resp->json();

        $userModel = config('auth.providers.users.model');

        $user = $userModel::firstOrCreate([
            'iin' => $userData['iin'] ?? null
        ], [
            'name' => $userData['name'] ?? null,
            'email' => $userData['email'] ?? null,
        ]);

        Auth::login($user, true);

        if (config('sso.token_storage') === 'session') {
            session()->put('sso_user_data', $userData);
        } elseif (config('sso.token_storage') === 'redis') {
            cache()->put('sso_user_'. $user->id, $userData, now()->addMinutes(60));
        }

        return redirect()->intended('/');
    }
}
