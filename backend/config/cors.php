<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['*'],

    'allowed_methods' => ['*'],

    /*
     * DESARROLLO LOCAL: FRONTEND_URL=http://localhost:5173
     * PRODUCCIÓN (Vercel + Railway):
     *   FRONTEND_URL=https://siderae-app.vercel.app
     *   CORS_ALLOWED_ORIGINS=https://siderae-app.vercel.app,https://tu-dominio.com
     *
     * CORS_ALLOWED_ORIGINS permite añadir múltiples orígenes separados por coma
     * sin modificar este archivo. Si no se define, se usa FRONTEND_URL como único origen.
     */
    'allowed_origins' => array_values(array_filter(
        array_map(
            'trim',
            explode(',', env('CORS_ALLOWED_ORIGINS', env('FRONTEND_URL', 'http://localhost:5173')))
        )
    )),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // Debe ser true para Sanctum SPA (cookies de sesión cross-origin)
    'supports_credentials' => true,

];
