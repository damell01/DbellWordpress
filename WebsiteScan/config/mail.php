<?php
// config/mail.php

return [
    'driver'     => env('MAIL_DRIVER', 'mail'),
    'from'       => env('MAIL_FROM', ''),
    'from_name'  => env('MAIL_FROM_NAME', 'VerityScan'),
    'smtp_host'  => env('SMTP_HOST', ''),
    'smtp_port'  => (int) env('SMTP_PORT', 587),
    'smtp_user'  => env('SMTP_USER', ''),
    'smtp_pass'  => env('SMTP_PASS', ''),
    'encryption' => env('SMTP_ENCRYPTION', 'tls'),
];
