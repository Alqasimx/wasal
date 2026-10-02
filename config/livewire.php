<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Temporary File Uploads
    |--------------------------------------------------------------------------
    |
    | Livewire uploads files to this temporary location before Filament stores
    | them in the public properties directory.
    |
    */
    'temporary_file_upload' => [
        'disk' => env('LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK', 'local'),
        'directory' => 'livewire-tmp',
        'rules' => 'file|mimes:jpg,jpeg,png,webp|max:10240',
        'middleware' => 'throttle:60,1',
    ],
];
