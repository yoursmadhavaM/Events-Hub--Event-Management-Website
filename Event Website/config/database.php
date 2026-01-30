<?php
/**
 * Database and app config.
 * For XAMPP subfolder (e.g. Event Website): set base_path to '/Event%20Website' or your folder name.
 * If app runs at document root: leave base_path as ''.
 */
return [
    // Use 'auto' to work with both XAMPP subfolders and `php -S localhost:8000`.
    // You can still hardcode it (e.g. '/Event%20Website') if you prefer.
    'base_path' => 'auto',
    'db' => [
        'host' => 'localhost',
        'name' => 'eventhub',
        'user' => 'root',
        'pass' => '',
        'charset' => 'utf8mb4',
    ],
];
