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
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

/**
 * @property-read int $id
 * @property string $ulid
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string $phone
 * @property int $guest_count
 * @property CarbonImmutable $check_in
 * @property CarbonImmutable $check_out
 * @property \DateInterval|null $preparation_time
 * @property string $summary
 * @property array<string, string> $price_list
 * @property ReservationStatus $status
 * @property array<string, string>|null $visited_at
 * @property CarbonImmutable|null $replied_at
 * @property-read Collection<Message> $messages
 * @property-read int $nights
 * @property-read CarbonImmutable[] $reservedPeriod
 * @property-read CarbonImmutable[] $checkInPreparationTime
 * @property-read CarbonImmutable[] $checkOutPreparationTime
 */
class Reservation extends Model
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
        'visited_at',
        'replied_at',
    ];

    /**
     * @param  array  $attributes
     */
    final public function __construct(array $attributes = [])
    {
        $today = today()->format('Y-m-d');

        $this->attributes = [
            'check_in' => $today,
            'check_out' => $today,
            'guest_count' => 1,
        ];

        parent::__construct($attributes);
    }

    /**
     * @return Attribute
     */
    protected function nights(): Attribute
    {
        return Attribute::make(
            get: function () {
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
            'status' => ReservationStatus::class,
            'visited_at' => 'array',
            'replied_at' => 'immutable_datetime',
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
        $reservation = Session::get("reservation", new static());

        return is_array($reservation)
            ? new static($reservation)
            : $reservation;
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

    /**
     * @param  User  $user
     * @return bool
     */
    public function hasNewMessageFor(User $user): bool
    {
        if (! $this->visited_at || ! $this->replied_at) return false;
        // Given user has never visited the reservation...
        if (! array_key_exists($user->email, $this->visited_at)) return false;

        $visitedAt = new CarbonImmutable($this->visited_at[$user->email]);

        return $this->replied_at->greaterThan($visitedAt);
    }
}
