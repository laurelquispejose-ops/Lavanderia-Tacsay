<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Culqi Keys
    |--------------------------------------------------------------------------
    |
    | The Culqi public key and private key give you access to Culqi's
    | API. The "public" key is typically used when interacting with
    | Culqi.js while the "private" key accesses private API endpoints.
    |
    */
    'public_key' => env('CULQI_PUBLIC_KEY', ''),
    'private_key' => env('CULQI_PRIVATE_KEY', ''),
];
