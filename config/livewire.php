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
        // Filament performs the final image validation; Livewire only needs
        // to accept the temporary file and enforce the upload-size limit.
        'rules' => 'file|max:10240',
        'middleware' => 'throttle:60,1',
    ],
];
