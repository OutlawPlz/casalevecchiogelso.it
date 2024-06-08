<?php

namespace App\Models;

use App\Casts\AsDateInterval;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;

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
 * @property-read int $nights
 * @property-read \DateTimeImmutable[] $reservedPeriod
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

    protected $attributes = [
        'guest_count' => 1,
    ];

    /**
     * @return Attribute
     */
    protected function nights(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! $this->check_in || ! $this->check_out) return 0;

                return date_diff($this->check_in, $this->check_out)->d;
            }
        );
    }

    /**
     * @return Attribute
     */
    protected function reservedPeriod(): Attribute
    {
        return Attribute::make(
            get: fn () => [$this->check_in, $this->check_out]
        );
    }

    /**
     * @return Attribute
     */
    protected function order(): Attribute
    {
        return Attribute::make(
            get: fn () => [
                config('reservation.overnight_stay') => $this->nights,
                config('reservation.cleaning_fee'),
            ]
        );
    }

    /**
     * @return void
     */
    public function toSession(): void
    {
        foreach (['check_in', 'check_out', 'guest_count'] as $attribute) {
            Session::put("reservation.$attribute", $this->$attribute);
        }
    }

    /**
     * @return static
     */
    public static function fromSession(): static
    {
        $attributes = Session::get("reservation");

        return new static($attributes);
    }
}
