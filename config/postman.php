<?php

return [
    'apiURL' => env('LARAVEL_API_URL', ''),
    'collectionName' => env('LARAVEL_POSTMAN_COLLECTION_NAME', env('APP_NAME', 'Laravel API')),
    'collectionDescription' => env("LARAVEL_POSTMAN_COLLECTION_DESCRIPTION", ''),
    'apiPrefix' => env('LARAVEL_POSTMAN_API_PREFIX', 'api,oauth'),
    'ignore' => env('LARAVEL_POSTMAN_API_PREFIX_IGNORE', '_ignition'),
    'skipHEAD' => env('LARAVEL_POSTMAN_SKIP_HEAD', true),
    'exportDirectory' => env('LARAVEL_POSTMAN_EXPORT_DIRECTORY', storage_path()),
];
