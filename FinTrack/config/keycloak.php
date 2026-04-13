<?php

$baseUrl = rtrim((string) env('KEYCLOAK_BASE_URL', ''), '/');
$realm = (string) env('KEYCLOAK_REALM', 'fintech');
$issuer = rtrim((string) env('KEYCLOAK_ISSUER', $baseUrl !== '' ? $baseUrl.'/realms/'.$realm : ''), '/');
$realmBase = $baseUrl !== ''
    ? $baseUrl.'/realms/'.$realm.'/protocol/openid-connect'
    : '';

return [
    'enabled' => (bool) env('KEYCLOAK_ENABLED', false),
    'auth_mode' => (string) env('AUTH_MODE', 'legacy'),

    'base_url' => $baseUrl,
    'realm' => $realm,
    'issuer' => $issuer,
    'client_id' => (string) env('KEYCLOAK_CLIENT_ID', ''),
    'client_secret' => (string) env('KEYCLOAK_CLIENT_SECRET', ''),

    'redirect_uri' => (string) env('KEYCLOAK_REDIRECT_URI', ''),
    'post_logout_redirect_uri' => (string) env('KEYCLOAK_POST_LOGOUT_REDIRECT_URI', ''),

    'scopes' => array_values(array_filter(explode(' ', trim((string) env('KEYCLOAK_SCOPES', 'openid profile email'))))),
    'http_timeout' => (int) env('KEYCLOAK_HTTP_TIMEOUT', 10),

    'endpoints' => [
        'authorization' => (string) env(
            'KEYCLOAK_AUTHORIZATION_ENDPOINT',
            $realmBase !== '' ? $realmBase.'/auth' : ''
        ),
        'token' => (string) env(
            'KEYCLOAK_TOKEN_ENDPOINT',
            $realmBase !== '' ? $realmBase.'/token' : ''
        ),
        'userinfo' => (string) env(
            'KEYCLOAK_USERINFO_ENDPOINT',
            $realmBase !== '' ? $realmBase.'/userinfo' : ''
        ),
        'logout' => (string) env(
            'KEYCLOAK_LOGOUT_ENDPOINT',
            $realmBase !== '' ? $realmBase.'/logout' : ''
        ),
        'jwks' => (string) env(
            'KEYCLOAK_JWKS_ENDPOINT',
            $realmBase !== '' ? $realmBase.'/certs' : ''
        ),
    ],
];
