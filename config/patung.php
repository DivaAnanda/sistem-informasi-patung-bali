<?php

return [

    // Secret parts of the expert's link and the team's export link. Empty disables the feature.
    'expert_token' => env('EXPERT_ACCESS_TOKEN'),
    'export_token' => env('EXPORT_ACCESS_TOKEN'),

    // Photos uploaded through the admin panel go to a public Supabase Storage bucket through its S3 API.
    // The S3 key only reaches Storage, unlike the service_role key, which would bypass every database policy.
    'photo_storage' => [
        'endpoint' => env('SUPABASE_S3_ENDPOINT'),
        'region' => env('SUPABASE_S3_REGION', 'ap-southeast-1'),
        'bucket' => env('SUPABASE_STORAGE_BUCKET', 'patung'),
        'key' => env('SUPABASE_S3_KEY_ID'),
        'secret' => env('SUPABASE_S3_SECRET'),
        'public_url' => env('SUPABASE_STORAGE_PUBLIC_URL'),
    ],

];
