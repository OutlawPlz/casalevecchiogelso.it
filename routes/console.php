<?php

use App\Actions\ChargeOnDueDate;
use App\Actions\PayoutOnCheckIn;
use Illuminate\Support\Facades\Schedule;

Schedule::call(new PayoutOnCheckIn)->daily();

Schedule::call(new ChargeOnDueDate)->daily();
