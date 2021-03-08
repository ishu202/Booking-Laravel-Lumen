<?php

declare(strict_types=1);

return [

    // Manage autoload migrations
    'autoload_migrations' => false,

    // Booking Database Tables
    'tables' => [

        'tblrinfo' => 'tblrinfo',
        'tblmodorders' => 'tblmodorders',
        'tblrefundorder' => 'tblrefundorder',
        'tranaction' => 'transaction',
        'tblstripeCustomers' => 'tblstripeCustomers'

    ],

    'gateway' => [
        'stripe_api_key' => 'pk_test_51H1SQADx12Pt5KqrvEX4rp70xhcIJk44mtEEyywRRfeYW3L8oKppE0DMaNz2DeiSiC95AjQEWFT1OPGEuM0WnB9X00P59bXOw1',
        'stripe_api_secret' => 'sk_test_51H1SQADx12Pt5KqrUfIZr3menB4pBwA9l8kCf5b8OsHBetZUfYmPPOsbs24sQZ77sDQLmD0sBDrOCjpihf5zEu9I00JOOmPJbU'
    ],

    // Booking Models
    'models' => [
        'tblrinfo' => \R7\Booking\Models\Tblrinfo::class,
        'tblmodorders' => \R7\Booking\Models\Tblmodorders::class,
        'tblrefundorder' => \R7\Booking\Models\Tblmodorders::class,
        'tranaction' => \R7\Booking\Models\Transaction::class,
        'tblstripeCustomers' => \R7\Booking\Models\TblstripeCustomers::class
    ],

];
