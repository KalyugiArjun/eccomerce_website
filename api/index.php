<?php

// Vercel Serverless Handler for Laravel
// Prepare writable storage directory in /tmp for serverless environment
$storageDirs = [
    '/tmp/storage/framework/views',
    '/tmp/storage/framework/cache',
    '/tmp/storage/framework/cache/data',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/logs',
    '/tmp/database',
];

foreach ($storageDirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

// Copy sqlite database if it exists and not already in /tmp
$localDb = __DIR__ . '/../database/database.sqlite';
$tmpDb = '/tmp/database/database.sqlite';
if (!file_exists($tmpDb)) {
    if (file_exists($localDb)) {
        @copy($localDb, $tmpDb);
    } else {
        @touch($tmpDb);
    }
}

putenv('APP_STORAGE_PATH=/tmp/storage');
putenv('DB_DATABASE=/tmp/database/database.sqlite');
$_ENV['APP_STORAGE_PATH'] = '/tmp/storage';
$_ENV['DB_DATABASE'] = '/tmp/database/database.sqlite';

// Forward request to Laravel public index
require __DIR__ . '/../public/index.php';
