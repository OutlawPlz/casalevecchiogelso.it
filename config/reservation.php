<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Preparation Time
    |--------------------------------------------------------------------------
    |
    | How many days before and after each reservation do you need to block.
    |
    */

    'preparation_time' => env('PREPARATION_TIME', 1),

    /*
    |--------------------------------------------------------------------------
    | Default Check-in/Check-out Time
    |--------------------------------------------------------------------------
    |
    | This value is the default check-in and check-out time. This value is
    | used when the application needs to determine the reserved period. The
    | check-in date + check-in time + preparation time determines reserved
    | period. The same happens for check-out.
    |
    */

    'check_in_time' => env('CHECK_IN_TIME', '11:00'),

    'check_out_time' => env('CHECK_OUT_TIME', '16:00'),
];
