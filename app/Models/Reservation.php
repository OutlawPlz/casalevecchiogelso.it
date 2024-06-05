<?php

namespace App\Models;

use App\Casts\AsDateInterval;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read int $id
 * @property string $uid
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string $phone
 * @property int $guest_count
 * @property \DateTimeImmutable $check_in
 * @property \DateTimeImmutable $check_out
 * @property \DateInterval|null $preparation_time
 * @property string $summary
 * @property array $price_list
 * @property-read int $total
 * @property-read int $nights
 * @property-read \DateTimeImmutable[] $reserved_period
 * @property-read \DateTimeImmutable[] $check_in_preparation_time
 * @property-read \DateTimeImmutable[] $check_out_preparation_time
 */
class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'ulid',
        'first_name',
        'last_name',
        'email',
        'guest_count',
        'check_in',
        'check_out',
        'preparation_time',
        'price_list',
        'summary'
    ];

    protected $casts = [
        'check_in' => 'immutable_date',
        'check_out' => 'immutable_date',
        'preparation_time' => AsDateInterval::class,
        'price_list' => 'array',
    ];

    protected function nights(): Attribute
    {
        return Attribute::make(
            get: fn (null $value, array $attributes) => date_diff($this->check_in, $this->check_out)->d
        );
    }

    protected function checkInPreparationTime(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! $this->preparation_time) return [];

                return [
                    $this->check_in->sub($this->preparation_time),
                    $this->check_in
                ];
            }
        );
    }

    protected function checkOutPreparationTime(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! $this->preparation_time) return [];

                return [
                    $this->check_out,
                    $this->check_out->add($this->preparation_time)
                ];
            }
        );
    }

    protected function reservedPeriod(): Attribute
    {
        return Attribute::make(
            get: function () {
                return [$this->check_in, $this->check_out];
            }
        );
    }

    protected function total(): Attribute
    {
        return Attribute::make(
            get: function () {
                $priceList = $this->price_list;

                $total = $priceList['price_per_night'] * $this->nights;

                unset($priceList['price_per_night']);

                $total += array_sum($priceList);

                return $total;
            }
        );
    }
}
