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

    'allowed_origins' => [
        // Développement local
        'http://localhost',
        'http://localhost:3000',   // Nuxt JS dev
        'http://localhost:8080',
        'http://127.0.0.1',
        'http://127.0.0.1:3000',
        // Domaine de développement local
        'http://backend-citoyen.test',
        'http://frontend-citoyen.test',
        // En production : remplacer par les domaines réels
        // 'https://app.citoyen.tg',
        // 'https://admin.citoyen.tg',
    ],

    'allowed_origins_patterns' => [
        // Autorise toutes les origines locales (développement)
        '#^http://localhost(:\d+)?$#',
        '#^http://127\.0\.0\.1(:\d+)?$#',
        // Application mobile Flutter (pas d'origine HTTP classique)
        // Flutter envoie une origine vide ou null → géré par wildcard ci-dessous
    ],

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
