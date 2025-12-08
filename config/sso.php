<?php
return [
    'server_url' => env('SSO_AUTH_SERVER', 'https://auth.example.com'),

    'authorize_endpoint' => '/sso-auth',
    'token_endpoint' => '/api/auth/resource',
    'exchange_endpoint' => '/api/sso/exchange-code',

    'client_id' => env('SSO_CLIENT_ID'),
    'client_secret' => env('SSO_CLIENT_SECRET'),

    'redirect_uri' => env('SSO_REDIRECT_URI', '/'),

    'state_key' => '_sso_state',

    'token_storage' => env('SSO_TOKEN_STORAGE', 'session'),
];
