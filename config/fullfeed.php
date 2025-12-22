<?php

declare(strict_types=1);

return [
    /**
     * Site rules JSON file paths.
     */
    'paths' => [
        // The first rule found will be used, so place the files you want to prioritize at the top.
        // resource_path('fullfeed/your-rules.json'),

        resource_path('fullfeed/plus.json'),
        resource_path('fullfeed/items_all.json'),
    ],

    'user_agent' => env('FULLFEED_USER_AGENT'),
];
