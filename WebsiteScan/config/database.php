<?php
// config/database.php

return [
    'host'    => env('DB_HOST', 'localhost'),
    'port'    => env('DB_PORT', '3306'),
    'name'    => env('DB_NAME', 'sitescope'),
    'user'    => env('DB_USER', 'root'),
    'pass'    => env('DB_PASS', ''),
    'charset' => 'utf8mb4',
];
