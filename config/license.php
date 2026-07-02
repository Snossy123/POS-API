<?php

return [

    /*
    |--------------------------------------------------------------------------
    | License Enforcement
    |--------------------------------------------------------------------------
    |
    | Set to false during local development. In production this should be true.
    |
    */
    'enforce' => env('LICENSE_ENFORCE', env('APP_ENV') === 'production'),

    /*
    |--------------------------------------------------------------------------
    | Machine ID File
    |--------------------------------------------------------------------------
    |
    | Written by activate.ps1 on the client host and mounted into the container.
    |
    */
    'machine_id_path' => storage_path('app/license/machine-id.txt'),

    /*
    |--------------------------------------------------------------------------
    | RSA Public Key (PEM)
    |--------------------------------------------------------------------------
    |
    | Used to verify license signatures. Safe to ship with the application.
    | Generate a key pair once and keep LICENSE_PRIVATE_KEY on the developer
    | machine only.
    |
    */
    'public_key_path' => storage_path('app/license/public.pem'),

];
