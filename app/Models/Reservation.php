<?php

namespace App\Models;

use App\Casts\AsDateInterval;
use App\Enums\ReservationStatus;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Session;

/**
 * @property-read int $id
 * @property string $ulid
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string $phone
 * @property int $guest_count
 * @property ?CarbonImmutable $check_in
 * @property ?CarbonImmutable $check_out
 * @property \DateInterval|null $preparation_time
 * @property string $summary
 * @property array $messages
 * @property array<string, string> $price_list
 * @property ReservationStatus $status
 * @property-read int $nights
 * @property-read CarbonImmutable[] $reservedPeriod
 * @property-read CarbonImmutable[] $checkInPreparationTime
 * @property-read CarbonImmutable[] $checkOutPreparationTime
 */
final class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'ulid',
        'user_id',
        'name',
        'email',
        'phone',
        'guest_count',
        'check_in',
        'check_out',
        'preparation_time',
        'price_list',
        'summary',
        'status',
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
     * @return string[]
     */
    protected function casts(): array
    {
        return [
            'check_in' => 'immutable_date',
            'check_out' => 'immutable_date',
            'preparation_time' => AsDateInterval::class,
            'price_list' => 'array',
            'messages' => 'array',
            'status' => ReservationStatus::class,
        ];
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
     * @return array
     */
    public function order(): array
    {
        $order = [];

        foreach ($this->price_list as $productId => $priceId) {
            $order[] = [
                'price' => $priceId,
                'quantity' => is_overnight_stay($productId) ? $this->nights : 1
            ];
        }

        return $order;
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
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return Attribute
     */
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

    /**
     * @return Attribute
     */
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

    /**
     * @return static
     */
    public static function fromSession(): static
    {
        $attributes = Session::get("reservation", [
            'check_in' => today(),
            'check_out' => today(),
            'guest_count' => 1,
        ]);

        return new static($attributes);
    }

    /**
     * @return HasMany
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * @param  string|ReservationStatus  $status
     * @return bool
     */
    public function inStatus(string|ReservationStatus $status): bool
    {
        if (is_string($status)) {
            $status = ReservationStatus::from($status);
        }

        return $this->status === $status;
    }
}
