<?php

return [
    'push_driver' => env('PUSH_DRIVER', 'disabled'),
    'firebase_credentials' => env('FIREBASE_CREDENTIALS'),
    'firebase_project_id' => env('FIREBASE_PROJECT_ID'),
    'location_max_age_minutes' => 30,
];
