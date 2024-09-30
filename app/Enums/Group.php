<?php

namespace App\Enums;

final class Group
{
    const STATUS_ACTIVE = 1;
    const STATUS_DE_ACTIVE = 2;

    const STATUS_PUBLIC = 'public';
    const STATUS_PRIVATE = 'private';
    
    const STATUS_PAYMENT_REQUIRED = 1;
    const STATUS_PAYMENT_LATER = 0;

    const STATUS_REQUESTED = 'false';
    const STATUS_ACCEPTED = 'true';
    const STATUS_REJECTED = 'rejected';

    const RATE_ANCIENT = 'ancient';
    const RATE_EMERGING = 'emerging';
    const RATE_NEWLY_ESTABLISHED = 'newly established';

    const COLOR_OF_RATE = [
        'ancient' => 'ancient',
        'emerging' => 'emerging',
        'newly established' => 'newly'
    ];
}
