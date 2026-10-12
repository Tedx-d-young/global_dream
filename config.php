<?php
declare(strict_types=1);

/*
 * Global Dream configuration.
 * Use environment variables on production hosting whenever possible.
 */
return [
    'db' => [
        'host' => getenv('GD_DB_HOST') ?: '127.0.0.1',
        'name' => getenv('GD_DB_NAME') ?: 'global_dream',
        'user' => getenv('GD_DB_USER') ?: 'root',
        'pass' => getenv('GD_DB_PASS') ?: '',
        'charset' => 'utf8mb4',
    ],
    'app' => [
        'base_url' => rtrim(getenv('GD_BASE_URL') ?: '', '/'),
        'timezone' => getenv('GD_TIMEZONE') ?: 'Africa/Addis_Ababa',
        'session_secure' => filter_var(getenv('GD_SESSION_SECURE') ?: '0', FILTER_VALIDATE_BOOLEAN),
    ],
    'discovery' => [
        // Keep this secret. Use a long random value in production.
        'cron_token' => getenv('GD_CRON_TOKEN') ?: 'CHANGE_THIS_TO_A_LONG_RANDOM_SECRET_BEFORE_USE',
        'max_items_per_feed' => 25,
        'max_feed_bytes' => 2000000,
        'request_timeout' => 12,
    ],
];
