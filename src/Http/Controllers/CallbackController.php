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

    protected $ssoClient;

    public function __construct(SsoClient $ssoClient){
        $this->ssoClient = $ssoClient;
    }

    public function handle(Request $request)
    {

        if ($request->has('error')) {
            return redirect('/')->with('error', $request->get('error_description') ?: $request->get('error'));
        }

        $code = $request->get('code');

        try {

            $resp = Http::withToken($this->ssoClient->getAccessToken())->get(rtrim(config('sso.server_url'),'/').config('sso.exchange_endpoint'), [
                'code' => $code,
            ]);

            if ($resp->failed()) {
                Log::error('Token exchange failed', ['exception' => $resp->body(), 'ip' => $request->ip()]);
                abort(403, 'Token exchange failed');
            }

        } catch (\Throwable $exception) {

            Log::error('Не удалось получить токен', ['exception' => $exception]);

            return redirect()->intended(config('sso.redirect_uri'))->withErrors('Не удалось получить токен');
        }

        $userData = $resp->json();

        $user = $this->findOrMergeUserData($userData);

        Log::info('Успешно полученные данные при авторизации SSO', ['exception' => $resp->body(), 'ip' => $request->ip(), 'user-model' => $user, 'guard' => Auth::guard()->getName()]);

        Auth::guard('web')->login($user);

        if (!Auth::check()) {
            Log::error('Пользователь по SSO не авторизован', ['exception' => $resp->body(), 'ip' => $request->ip(), 'user-model' => $user]);
        }

        if (config('sso.token_storage') === 'session') {
            session()->put('sso_user_data', $userData);
        } elseif (config('sso.token_storage') === 'redis') {
            cache()->put('sso_user_'. $user->id, $userData, now()->addMinutes(60));
        }

        return redirect()->intended(config('sso.redirect_uri'));
    }

    private function findOrMergeUserData(array $userData) {

        $userModel = config('auth.providers.users.model');

        $iin   = $userData['iin'];
        $email = $userData['email'];

        $user = $userModel::where('iin', $iin)
            ->orWhere('email', $email)
            ->first();

        if ($user) {
            $user->fill([
                'iin'   => $user->iin   ?: $iin,
                'email' => $user->email ?: $email,
            ]);

            if ($user->isDirty()) {
                $user->save();
            }

            return $user;
        }

        return $userModel::create([
            'iin'   => $iin,
            'email' => $email,
            'name'  => $userData['name'] ?? null,
        ]);
    }
}
