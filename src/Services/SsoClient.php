<?php

namespace USO\SsoClient\Services;

use Illuminate\Support\Facades\Http;
use Firebase\JWT\JWT;

class SsoClient
{
    protected $config;
    protected $http;

    public function __construct()
    {
        $this->config = config('sso');
        $this->http = Http::timeout(10);
    }

    public function getAccessToken()
    {

        if (cache()->has('sso_access_token')) {
            return cache()->get('sso_access_token');
        } else {

            $resp = $this->http->asForm()->post(rtrim($this->config['server_url'],'/') . $this->config['token_endpoint'], [
                'client_id' => $this->config['client_id'],
                'client_secret' => $this->config['client_secret'],
            ]);

            if ($resp->failed()) {
                throw new \Exception('Token request failed: ' . $resp->body());
            }

            cache()->put('sso_access_token', $resp->json()['access_token'], $resp->json()['expires_in']);

            return $resp->json()['access_token'];
        }
    }

    public function redirectToSso()
    {
        $redirect = rtrim($this->config['server_url'],'/') . $this->config['authorize_endpoint'] . '?redirect_uri=' . route('sso.callback');

        return redirect($redirect);
    }
}
