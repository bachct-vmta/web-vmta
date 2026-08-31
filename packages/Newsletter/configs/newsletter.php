<?php

return [
    // Throttle subscribe endpoint: 3 attempts / 60 minutes / IP (anti-spam).
    'throttle' => env('NEWSLETTER_THROTTLE', '3,60'),
];
