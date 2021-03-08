<?php

declare(strict_types=1);

return [

    // Manage autoload migrations
    'autoload_migrations' => false,

    // Booking Database Tables
    'tables' => [

        'booking' => 'tblrinfo',
        'mod_booking' => 'tblmodorders',
        'edit_booking' => 'tblrefundorder',
        'stripe_customer_table' => 'tblstripeCustomers'

    ],

    'gateway' => [
        'stripe_api_key' => 'pk_test_51H1SQADx12Pt5KqrvEX4rp70xhcIJk44mtEEyywRRfeYW3L8oKppE0DMaNz2DeiSiC95AjQEWFT1OPGEuM0WnB9X00P59bXOw1',
        'stripe_api_secret' => 'sk_test_51H1SQADx12Pt5KqrUfIZr3menB4pBwA9l8kCf5b8OsHBetZUfYmPPOsbs24sQZ77sDQLmD0sBDrOCjpihf5zEu9I00JOOmPJbU'
    ],

    // Booking Models
    'models' => [
        'booking' => \R7\Booking\Models\Tblrinfo::class,
        'mod_booking' => \R7\Booking\Models\Tblmodorders::class,
        'refund_booking' => \R7\Booking\Models\Tblmodorders::class,
        'transaction' => \R7\Booking\Models\Transaction::class
    ],

];
