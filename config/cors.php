<?php

/*
|--------------------------------------------------------------------------
| CORS Configuration
|--------------------------------------------------------------------------
|
| Autorise les requêtes cross-origin depuis l'application mobile Flutter
| (via Dio/http) et la plateforme web Nuxt JS.
|
*/

return [

    /*
    |----------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |----------------------------------------------------------------------
    |
    | 'paths'             => Les routes concernées par CORS
    | 'allowed_methods'   => Méthodes HTTP autorisées
    | 'allowed_origins'   => Domaines autorisés (* = tous)
    | 'allowed_headers'   => En-têtes autorisés
    | 'exposed_headers'   => En-têtes exposés au client
    | 'max_age'           => Durée du cache preflight (secondes)
    | 'supports_credentials' => Autoriser les cookies/credentials
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => ['*'],

    'allowed_headers' => [
        'Content-Type',
        'Accept',
        'Authorization',
        'X-Requested-With',
        'X-CSRF-TOKEN',
        'Origin',
    ],

    'exposed_headers' => [],

    'max_age' => 86400, // 24 heures

    'supports_credentials' => true,
];
