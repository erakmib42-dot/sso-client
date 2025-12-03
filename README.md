# sso-client
1. composer require vendor/laravel-sso-client
2. php artisan vendor:publish --provider="USO\\SSOClient\\SSOServiceProvider" --tag=config
3. Fill .env: SSO_SERVER_URL, SSO_CLIENT_ID, SSO_CLIENT_SECRET, SSO_REDIRECT_URI, SSO_TOKEN_STORAGE(session|redis)
