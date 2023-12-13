<?php

namespace App\Models;

use App\Casts\AsDateInterval;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property \DateTimeImmutable $check_in
 * @property \DateTimeImmutable $check_out
 * @property \DateInterval|null $preparation_time
 * @property-read int $nights
 * @property-read \DateTimeImmutable|\DateTimeImmutable[]|null $check_in_preparation_time
 * @property-read \DateTimeImmutable|\DateTimeImmutable[]|null $check_out_preparation_time
 */
class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'uid',
        'first_name',
        'last_name',
        'email',
        'phone',
        'guests_count',
        'check_in',
        'check_out',
        'preparation_time',
        'summary'
    ];

    protected $casts = [
        'check_in' => 'immutable_date',
        'check_out' => 'immutable_date',
        'preparation_time' => AsDateInterval::class,
    ];

    /**
     * The nights spent at the hotel.
     *
     * @return Attribute
     */
    protected function nights(): Attribute
    {
        return Attribute::make(
            get: fn (null $value, array $attributes) => date_diff($this->check_in, $this->check_out)->d
        );
    }

    /**
     * @return Attribute
     */
    protected function checkInPreparationTime(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! $this->preparation_time) return null;

                $preparationFrom = $this->check_in->sub($this->preparation_time);

                if ($this->preparation_time->d === 1) return $preparationFrom;

                $preparationTo = $this->check_in->sub(new \DateInterval('P1D'));

                return [$preparationFrom, $preparationTo];
            }
        );
    }

    /**
     * @return Attribute
     */
    protected function checkOutPreparationTime(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! $this->preparation_time) return null;

                $preparationFrom = $this->check_out->add($this->preparation_time);

                if ($this->preparation_time->d === 1) return $preparationFrom;

                $preparationTo = $this->check_out->add(new \DateInterval('P1D'));

                return [$preparationFrom, $preparationTo];
            }
        );
    }
}
