<?php

return [
    'guest' => [
        'characters_per_version' => env('CODEDIFF_GUEST_CHARACTER_LIMIT', 10000000),
        'requests_per_minute' => env('CODEDIFF_GUEST_REQUESTS_PER_MINUTE', 30),
    ],
    'free' => [
        'characters_per_version' => env('CODEDIFF_FREE_CHARACTER_LIMIT', 50000),
        'comparisons_per_month' => env('CODEDIFF_FREE_COMPARISONS_PER_MONTH', 50),
        'saved_comparisons' => env('CODEDIFF_FREE_SAVED_COMPARISONS', 5),
    ],
    'lifetime' => [
        'characters_per_version' => env('CODEDIFF_LIFETIME_CHARACTER_LIMIT', 500000),
    ],
    'max_lines_per_version' => env('CODEDIFF_MAX_LINES', 10000),
];
