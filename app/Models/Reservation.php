<?php

namespace App\Models;

use App\Casts\AsDateInterval;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property \DateTime $check_in
 * @property \DateTime $check_out
 * @property \DateInterval[] $preparation_time
 * @property-read int $nights
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
            get: fn (null $value, array $attributes) => date_diff(
                new \DateTime($attributes['check_in']),
                new \DateTime($attributes['check_out'])
            )->d
        );
    }
}
