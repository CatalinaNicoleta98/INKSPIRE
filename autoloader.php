<?php

spl_autoload_register(function ($class) {

    $class = str_replace('\\', '/', $class);
    // If class is namespaced (e.g. PHPMailer/PHPMailer/PHPMailer),
    // reduce to final segment so it matches file names like PHPMailer.php
    if (strpos($class, '/') !== false) {
        $parts = explode('/', $class);
        $class = end($parts);
    }

    // Special case: load config.php for Database class
    if ($class === 'Database') {
        require_once __DIR__ . '/config.php';
        return;
    }

    // Directories to search for class files
    $directories = [
        __DIR__ . '/',
        __DIR__ . '/Controllers/',
        __DIR__ . '/Models/',
        __DIR__ . '/helpers/',
        __DIR__ . '/helpers/PHPMailer/',
    ];

    foreach ($directories as $directory) {
        $file = $directory . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});
