<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Pricing
    |--------------------------------------------------------------------------
    |
    | Prices refers to Stripe prices ids. You should create those prices
    | upfront in Stripe, then reference those prices by the id.
    */

    'overnight_stay' => env('OVERNIGHT_STAY'),

    'cleaning_fee' => env('CLEANING_FEE'),

    /*
    |--------------------------------------------------------------------------
    | Default Preparation Time
    |--------------------------------------------------------------------------
    |
    | How many days before and after each reservation do you need to block.
    | The interval must be in iso8601 format. E.g. Use "P2D" for 2 days.
    |
    */

    'preparation_time' => env('PREPARATION_TIME', 'P1D'),

    /*
    |--------------------------------------------------------------------------
    | Default Check-in/Check-out Time
    |--------------------------------------------------------------------------
    |
    | This value is the default check-in and check-out time.
    |
    */

    'check_in_time' => env('CHECK_IN_TIME', '11:00'),

    'check_out_time' => env('CHECK_OUT_TIME', '16:00'),
];
