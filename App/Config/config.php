<?php
return [
    'app' => [
        'name' => 'LeBonPlan',
        'version' => '1.0.0',
        'env' => getenv('APP_ENV') ?: 'development',
        'debug' => getenv('APP_DEBUG') ?: true,
        'timezone' => 'Europe/Paris',
        'url' => getenv('APP_URL') ?: 'http://localhost',
    ],
    'database' => [
        'host' => getenv('DB_HOST') ?: 'localhost',
        'database' => getenv('DB_NAME') ?: 'aqghqfnk_test',
        'username' => getenv('DB_USER') ?: 'root',
        'password' => getenv('DB_PASS') ?: '',
        'charset' => 'utf8mb4',
    ],
    'mail' => [
        'host' => getenv('MAIL_HOST') ?: '',
        'port' => getenv('MAIL_PORT') ?: 587,
        'username' => getenv('MAIL_USER') ?: '',
        'password' => getenv('MAIL_PASS') ?: '',
        'encryption' => getenv('MAIL_ENCRYPTION') ?: 'tls',
        'from_address' => getenv('MAIL_FROM_ADDRESS') ?: 'noreply@lebonplan.fr',
        'from_name' => getenv('MAIL_FROM_NAME') ?: 'LeBonPlan',
    ],
    'security' => [
        'session_lifetime' => 86400,
        'password_min_length' => 8,
        'max_login_attempts' => 5,
        'lockout_time' => 900,
    ],
    'uploads' => [
        'cv_path' => __DIR__ . '/../../storage/uploads/cv/',
        'max_file_size' => 5 * 1024 * 1024,
        'allowed_extensions' => ['pdf', 'doc', 'docx'],
    ],
];