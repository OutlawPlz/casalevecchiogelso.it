<?php

namespace App\Traits;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * @property CarbonImmutable $check_in
 * @property CarbonImmutable $check_out
 * @property-read int $nights
 * @property-read CarbonImmutable[] $reservedPeriod
 * @property-read CarbonImmutable[] $checkInPreparationTime
 * @property-read CarbonImmutable[] $checkOutPreparationTime
 */
trait HasStartEndDates
{
    public function initializeHasStartEndDates(): void
    {
        $this->mergeFillable(['check_in', 'check_out']);

        $this->mergeCasts([
            'check_in' => 'immutable_datetime',
            'check_out' => 'immutable_datetime',
        ]);
    }
    protected function nights(): Attribute
    {
        return Attribute::make(
            get: function () {
                return date_diff($this->check_in, $this->check_out)->d;
            }
        );
    }

    protected function reservedPeriod(): Attribute
    {
        return Attribute::make(
            get: fn () => [$this->check_in, $this->check_out]
        );
    }

    protected function checkInPreparationTime(): Attribute
    {
        return Attribute::make(
            get: function () {
                $preparationTime = config('reservation.preparation_time');

                if (! $preparationTime) return [];

                return [
                    $this->check_in->sub($preparationTime),
                    $this->check_in
                ];
            }
        );
    }

    protected function checkOutPreparationTime(): Attribute
    {
        return Attribute::make(
            get: function () {
                $preparationTime = config('reservation.preparation_time');

                if (! $preparationTime) return [];

                return [
                    $this->check_out,
                    $this->check_out->add($preparationTime)
                ];
            }
        );
    }

    public function inProgress(): bool
    {
        return now()->between($this->check_in, $this->check_out);
    }
}
