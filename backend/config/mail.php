<?php

return [
    // log (default, no account needed) | smtp (any SMTP server, e.g. Mailpit or a real provider) | array (tests)
    'default' => env('MAIL_MAILER', 'log'),

    'mailers' => [
        'log' => ['transport' => 'log', 'channel' => env('MAIL_LOG_CHANNEL')],
        'smtp' => [
            'transport' => 'smtp',
            'scheme' => env('MAIL_SCHEME'),
            'url' => env('MAIL_URL'),
            'host' => env('MAIL_HOST', '127.0.0.1'),
            'port' => (int) env('MAIL_PORT', 1025),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => 10,
        ],
        'array' => ['transport' => 'array'],
    ],

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'catalogue@example.test'),
        'name' => env('MAIL_FROM_NAME', 'Product Catalogue'),
    ],

    // Recipient of product enquiry emails.
    'sales_inbox' => env('SALES_INBOX', 'sales@example.test'),
];
