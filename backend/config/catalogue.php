<?php

return [
    // Which ProductProvider implementation to bind (see AppServiceProvider).
    'provider' => env('PRODUCTS_PROVIDER', 'fake_store'),

    // Seconds to cache successful upstream product responses.
    'cache_ttl' => (int) env('CATALOGUE_CACHE_TTL', 300),

    // Enquiry submissions allowed per client IP per minute.
    'enquiries_per_minute' => (int) env('ENQUIRY_RATE_LIMIT', 5),
];
