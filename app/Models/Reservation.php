<?php

namespace App\Models;

use App\Casts\AsDateInterval;
use App\Casts\AsDatePeriod;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property \DateTime $check_in
 * @property \DateTime $check_out
 * @property \DateInterval[] $preparation_time
 * @property-read \DatePeriod $reservedPeriod
 */
class Reservation extends Model
{
    use HasFactory;

    protected $fillable = ['uid', 'check_in', 'check_out', 'guests_count', 'preparation_time', 'summary'];

    protected $casts = [
        'check_in' => 'immutable_date',
        'check_out' => 'immutable_date',
        'reserved_period' => AsDatePeriod::class
    ];

    /**
     * @return Attribute
     */
    protected function preparationTime(): Attribute
    {
        return Attribute::make(
            get: fn () => '',
            set: fn() => ''
        );
    }

//    /**
//     * @return Attribute
//     */
//    protected function reservedPeriod(): Attribute
//    {
//        return Attribute::make(
//            get: function (mixed $value, array $attributes) {
//                $preparationTime = new \DateInterval($attributes['preparation_time']);
//
//                $startAt = (new \DateTime($attributes['check_in']))
//                    ->sub($preparationTime);
//
//                $endAt = (new \DateTime($attributes['check_out']))
//                    ->add($preparationTime);
//
//                $oneDayInterval = new \DateInterval('P1D');
//
//                return new \DatePeriod($startAt, $oneDayInterval, $endAt);
//            }
//        );
//    }
}
