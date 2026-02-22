<?php

// config for CCK/FilamentShot
return [

    'viewport' => [
        'width' => 1024,
        'height' => 768,
        'device_scale_factor' => 2,
    ],

    'theme' => [
        'dark_mode' => false,
        'primary_color' => '#6366f1',
    ],

    'browsershot' => [
        'node_binary' => null,
        'npm_binary' => null,
        'chrome_path' => null,
        'no_sandbox' => false,
        'timeout' => 60,
        'additional_options' => [],
    ],

    'css' => [
        'theme_path' => null,
        'extra' => '',
    ],

];
