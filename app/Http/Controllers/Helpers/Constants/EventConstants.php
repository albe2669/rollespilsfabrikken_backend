<?php

namespace App\Http\Controllers\Helpers\Constants;

class EventConstants
{
    public static array $recurrenceStringLookup = [
        null => 'none',
        0 => 'none',
        86400 => 'daily',
        604800 => 'weekly',
        1209600 => 'biweekly',
        1814400 => 'triweekly',
        2419200 => 'monthly',
        31536000 => 'yearly',
    ];

    public static array $recurrenceIntervalLookup = [
        'daily' => 86400,
        'weekly' => 604800,
        'biweekly' => 1209600,
        'triweekly' => 1814400,
        'monthly' => 2419200,
        'yearly' => 31536000,
    ];
}
